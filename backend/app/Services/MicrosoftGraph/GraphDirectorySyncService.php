<?php

namespace App\Services\MicrosoftGraph;

use App\Models\Department;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GraphDirectorySyncService
{
    private const GRAPH_BASE_URL = 'https://graph.microsoft.com/v1.0';

    public function sync(): array
    {
        $this->ensureConfigured();

        $token = $this->accessToken();
        $users = $this->fetchUsers($token);
        $summary = [
            'users_created' => 0,
            'users_updated' => 0,
            'departments_created' => 0,
            'groups_created' => 0,
            'groups_updated' => 0,
            'memberships_synced' => 0,
            'users_seen' => count($users),
            'users_skipped' => 0,
            'groups_seen' => 0,
        ];

        DB::transaction(function () use ($users, $token, &$summary) {
            foreach ($users as $graphUser) {
                $result = $this->upsertUser($graphUser);
                if ($result['skipped']) {
                    $summary['users_skipped']++;
                    continue;
                }

                $summary[$result['created'] ? 'users_created' : 'users_updated']++;
                $summary['departments_created'] += $result['department_created'] ? 1 : 0;
            }

            if (! config('services.microsoft_graph.sync_groups')) {
                return;
            }

            $groups = $this->fetchGroups($token);
            $summary['groups_seen'] = count($groups);

            foreach ($groups as $graphGroup) {
                $group = $this->upsertGroup($graphGroup);
                $summary[$group->wasRecentlyCreated ? 'groups_created' : 'groups_updated']++;

                $memberIds = $this->syncGroupMembers($token, $graphGroup, $group);
                $summary['memberships_synced'] += $memberIds;
            }
        });

        return $summary;
    }

    public function preview(string $token): array
    {
        return [
            'users' => collect($this->fetchUsers($token))
                ->filter(fn (array $user) => filled(($user['mail'] ?? null) ?: ($user['userPrincipalName'] ?? null)))
                ->map(fn (array $user) => [
                    'id' => $user['id'] ?? '',
                    'name' => $user['displayName'] ?? '',
                    'email' => Str::lower((string) (($user['mail'] ?? null) ?: ($user['userPrincipalName'] ?? ''))),
                    'department' => $user['department'] ?? '',
                    'title' => $user['jobTitle'] ?? '',
                    'company' => $user['companyName'] ?? '',
                    'enabled' => (bool) ($user['accountEnabled'] ?? true),
                ])
                ->sortBy('name')
                ->values()
                ->all(),
            'groups' => collect($this->fetchGroups($token))
                ->map(fn (array $group) => [
                    'id' => $group['id'] ?? '',
                    'name' => $group['displayName'] ?? '',
                    'code' => $this->groupCode($group),
                    'description' => $group['description'] ?? '',
                    'mail_enabled' => (bool) ($group['mailEnabled'] ?? false),
                    'security_enabled' => (bool) ($group['securityEnabled'] ?? false),
                ])
                ->sortBy('name')
                ->values()
                ->all(),
        ];
    }

    public function importSelected(string $token, array $selectedUserIds, array $selectedGroupIds): array
    {
        $selectedUserIds = array_values(array_filter($selectedUserIds));
        $selectedGroupIds = array_values(array_filter($selectedGroupIds));

        $summary = [
            'users_created' => 0,
            'users_updated' => 0,
            'users_skipped' => 0,
            'departments_created' => 0,
            'groups_created' => 0,
            'groups_updated' => 0,
            'memberships_synced' => 0,
        ];

        DB::transaction(function () use ($token, $selectedUserIds, $selectedGroupIds, &$summary) {
            $users = collect($this->fetchUsers($token))->keyBy('id');
            $groups = collect($this->fetchGroups($token))->keyBy('id');

            foreach ($selectedUserIds as $userId) {
                $graphUser = $users->get($userId);
                if (! $graphUser) {
                    $summary['users_skipped']++;
                    continue;
                }

                $result = $this->upsertUser($graphUser);
                if ($result['skipped']) {
                    $summary['users_skipped']++;
                    continue;
                }

                $summary[$result['created'] ? 'users_created' : 'users_updated']++;
                $summary['departments_created'] += $result['department_created'] ? 1 : 0;
            }

            foreach ($selectedGroupIds as $groupId) {
                $graphGroup = $groups->get($groupId);
                if (! $graphGroup) {
                    continue;
                }

                $group = $this->upsertGroup($graphGroup);
                $summary[$group->wasRecentlyCreated ? 'groups_created' : 'groups_updated']++;

                $members = $this->fetchGroupUserMembers($token, (string) $groupId);
                foreach ($members as $member) {
                    $result = $this->upsertUser($member);
                    if ($result['skipped']) {
                        $summary['users_skipped']++;
                        continue;
                    }

                    $summary[$result['created'] ? 'users_created' : 'users_updated']++;
                    $summary['departments_created'] += $result['department_created'] ? 1 : 0;
                }

                $summary['memberships_synced'] += $this->syncGroupMembersFromPayload($members, $group);
            }
        });

        return $summary;
    }

    private function ensureConfigured(): void
    {
        if (! config('services.microsoft_graph.sync_enabled')) {
            throw new RuntimeException('Microsoft 365 senkronizasyonu kapali. Ayarlardan ENTRA_SYNC_ENABLED=true yapin.');
        }

        foreach (['tenant_id', 'client_id', 'client_secret'] as $key) {
            if (blank(config("services.microsoft_graph.{$key}"))) {
                throw new RuntimeException("Microsoft Graph ayari eksik: {$key}.");
            }
        }
    }

    private function accessToken(): string
    {
        $tenantId = config('services.microsoft_graph.tenant_id');

        $response = Http::asForm()
            ->timeout(30)
            ->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
                'client_id' => config('services.microsoft_graph.client_id'),
                'client_secret' => config('services.microsoft_graph.client_secret'),
                'grant_type' => 'client_credentials',
                'scope' => 'https://graph.microsoft.com/.default',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Microsoft Graph token alinamadi: '.$response->body());
        }

        $token = $response->json('access_token');
        if (blank($token)) {
            throw new RuntimeException('Microsoft Graph token yanitinda access_token yok.');
        }

        return $token;
    }

    private function graph(string $token): PendingRequest
    {
        return Http::withToken($token)
            ->acceptJson()
            ->timeout(45)
            ->retry(2, 500);
    }

    private function fetchUsers(string $token): array
    {
        return $this->paginate($token, self::GRAPH_BASE_URL.'/users', [
            '$select' => implode(',', [
                'id',
                'displayName',
                'mail',
                'userPrincipalName',
                'jobTitle',
                'department',
                'companyName',
                'businessPhones',
                'mobilePhone',
                'officeLocation',
                'streetAddress',
                'city',
                'state',
                'country',
                'postalCode',
                'accountEnabled',
            ]),
            '$top' => 999,
        ]);
    }

    private function fetchGroups(string $token): array
    {
        $groups = $this->paginate($token, self::GRAPH_BASE_URL.'/groups', [
            '$select' => 'id,displayName,mailNickname,description,securityEnabled,mailEnabled',
            '$top' => 999,
        ]);

        $prefix = trim((string) config('services.microsoft_graph.group_prefix'));
        if ($prefix === '') {
            return $groups;
        }

        return array_values(array_filter($groups, function (array $group) use ($prefix): bool {
            return Str::startsWith((string) ($group['displayName'] ?? ''), $prefix)
                || Str::startsWith((string) ($group['mailNickname'] ?? ''), $prefix);
        }));
    }

    private function paginate(string $token, string $url, array $query = []): array
    {
        $items = [];

        do {
            $response = $this->graph($token)->get($url, $query);
            $query = [];

            if (! $response->successful()) {
                throw new RuntimeException('Microsoft Graph istegi basarisiz: '.$response->body());
            }

            $items = array_merge($items, $response->json('value', []));
            $url = $response->json('@odata.nextLink');
        } while (filled($url));

        return $items;
    }

    private function upsertUser(array $graphUser): array
    {
        $email = Str::lower((string) (($graphUser['mail'] ?? null) ?: ($graphUser['userPrincipalName'] ?? '')));
        if ($email === '' || ! str_contains($email, '@')) {
            return ['created' => false, 'department_created' => false, 'skipped' => true];
        }

        [$username, $domain] = explode('@', $email, 2);
        $department = null;
        $departmentCreated = false;
        $departmentName = trim((string) ($graphUser['department'] ?? ''));

        if ($departmentName !== '') {
            $department = Department::firstOrCreate(
                ['name' => $departmentName],
                ['description' => 'Microsoft 365 senkronizasyonu ile olusturuldu.', 'is_active' => true]
            );
            $departmentCreated = $department->wasRecentlyCreated;
        }

        $address = $this->compactAddress($graphUser);
        $phone = Arr::first($graphUser['businessPhones'] ?? []) ?: null;

        $user = User::where('email', $email)->first();
        $created = ! $user;

        $payload = [
            'entra_id' => $graphUser['id'] ?? null,
            'name' => $graphUser['displayName'] ?: $email,
            'username' => $username,
            'domain' => $domain,
            'department_id' => $department?->id,
            'title' => $graphUser['jobTitle'] ?? null,
            'company' => $graphUser['companyName'] ?? null,
            'phone' => $phone,
            'mobile' => $graphUser['mobilePhone'] ?? null,
            'office' => $graphUser['officeLocation'] ?? null,
            'address' => $address,
            'is_active' => (bool) ($graphUser['accountEnabled'] ?? true),
            'source' => 'entra',
        ];

        $user = User::updateOrCreate(['email' => $email], $payload);

        return ['created' => $created || $user->wasRecentlyCreated, 'department_created' => $departmentCreated, 'skipped' => false];
    }

    private function upsertGroup(array $graphGroup): Group
    {
        $code = $this->groupCode($graphGroup);

        return Group::updateOrCreate(
            ['code' => $code],
            [
                'entra_id' => $graphGroup['id'] ?? null,
                'name' => $graphGroup['displayName'] ?: $code,
                'description' => $graphGroup['description'] ?? null,
                'is_active' => true,
            ]
        );
    }

    private function syncGroupMembers(string $token, array $graphGroup, Group $group): int
    {
        $graphGroupId = $graphGroup['id'] ?? null;
        if (blank($graphGroupId)) {
            return 0;
        }

        $members = $this->fetchGroupUserMembers($token, (string) $graphGroupId);

        return $this->syncGroupMembersFromPayload($members, $group);
    }

    private function fetchGroupUserMembers(string $token, string $graphGroupId): array
    {
        return $this->paginate($token, self::GRAPH_BASE_URL."/groups/{$graphGroupId}/members/microsoft.graph.user", [
            '$select' => implode(',', [
                'id',
                'displayName',
                'mail',
                'userPrincipalName',
                'jobTitle',
                'department',
                'companyName',
                'businessPhones',
                'mobilePhone',
                'officeLocation',
                'streetAddress',
                'city',
                'state',
                'country',
                'postalCode',
                'accountEnabled',
            ]),
            '$top' => 999,
        ]);
    }

    private function syncGroupMembersFromPayload(array $members, Group $group): int
    {
        $emails = collect($members)
            ->map(fn (array $member) => Str::lower((string) (($member['mail'] ?? null) ?: ($member['userPrincipalName'] ?? ''))))
            ->filter(fn (string $email) => $email !== '' && str_contains($email, '@'))
            ->values();

        $userIds = User::whereIn('email', $emails)->pluck('id')->all();
        $group->users()->sync($userIds);

        return count($userIds);
    }

    private function compactAddress(array $graphUser): ?string
    {
        $parts = array_filter([
            $graphUser['streetAddress'] ?? null,
            $graphUser['city'] ?? null,
            $graphUser['state'] ?? null,
            $graphUser['postalCode'] ?? null,
            $graphUser['country'] ?? null,
        ], fn ($value) => filled($value));

        return $parts ? implode(', ', $parts) : null;
    }

    private function groupCode(array $graphGroup): string
    {
        $nickname = trim((string) ($graphGroup['mailNickname'] ?? ''));
        if ($nickname !== '') {
            $code = Str::slug($nickname, '_');
            if ($code !== '') {
                return $code;
            }
        }

        $displayName = trim((string) ($graphGroup['displayName'] ?? ''));
        if ($displayName !== '') {
            $code = Str::slug($displayName, '_');
            if ($code !== '') {
                return $code;
            }
        }

        return (string) ($graphGroup['id'] ?? Str::uuid());
    }
}
