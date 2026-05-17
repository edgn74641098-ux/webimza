<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Kullanicilar" subtitle="Kullanicilari bulun, filtreleyin ve ilgili aksiyonlari hizli alin.">
            <x-slot name="actions">
                <a href="{{ route('admin.users.create') }}"><x-ui.button>+ Kullanici Ekle</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell x-data="{ selectedUserId: localStorage.getItem('selectedUserId') || '' }">
        <x-ui.card>
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <x-ui.input name="q" :value="$filters['q'] ?? ''" placeholder="Kullanici, e-posta veya unvan ara" />
                <x-ui.select name="department_id">
                    <option value="">Tum departmanlar</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="status">
                    <option value="">Tum durumlar</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
                </x-ui.select>
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Filtrele</x-ui.button>
                    <a href="{{ route('admin.users.index') }}"><x-ui.button type="button" variant="secondary">Temizle</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($users->isEmpty())
                <x-ui.empty-state title="Henuz kullanici yok." description="Outlook eklentisi ilk calistiginda kullanici otomatik olusabilir veya manuel ekleyebilirsiniz.">
                    <x-slot name="action">
                        <a href="{{ route('admin.users.create') }}"><x-ui.button>+ Kullanici Ekle</x-ui.button></a>
                    </x-slot>
                </x-ui.empty-state>
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Kullanici</th>
                            <th>E-posta</th>
                            <th>Departman</th>
                            <th>Unvan</th>
                            <th>Telefon</th>
                            <th>Son gorulme</th>
                            <th>Son imza versiyonu</th>
                            <th>Durum</th>
                            <th class="text-right">Aksiyonlar</th>
                        </tr>
                    </x-slot>

                    @foreach($users as $user)
                        <tr x-bind:class="selectedUserId === '{{ (string) $user->id }}' ? 'bg-blue-50/70 ring-1 ring-inset ring-blue-200' : ''">
                            <td class="font-medium text-slate-900">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department?->name ?? '-' }}</td>
                            <td>{{ $user->title ?? '-' }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>{{ optional($user->addin_devices_max_last_seen_at)->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $user->last_signature_version ?? '-' }}</td>
                            <td>
                                <x-ui.badge :status="$user->is_active ? 'active' : 'inactive'">
                                    {{ $user->is_active ? 'Aktif' : 'Pasif' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-right">
                                <x-ui.dropdown>
                                    <x-slot name="trigger">
                                        <x-ui.button type="button" variant="secondary">Aksiyonlar</x-ui.button>
                                    </x-slot>
                                    <a href="{{ route('admin.users.show', $user) }}" @click="selectedUserId='{{ (string) $user->id }}'; localStorage.setItem('selectedUserId', selectedUserId)" class="block rounded px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Detay</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="block rounded px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Duzenle</a>
                                    <a href="{{ route('admin.updates.index', ['scope' => 'user', 'user_id' => $user->id]) }}" class="block rounded px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Guncelleme baslat</a>
                                </x-ui.dropdown>
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        {{ $users->links() }}
                    </x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
