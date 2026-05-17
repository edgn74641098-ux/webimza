<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::withCount('users');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        return view('admin.groups.index', [
            'groups' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
            'stats' => [
                'total' => Group::count(),
                'active' => Group::where('is_active', true)->count(),
                'members' => \DB::table('group_user')->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('admin.groups.form', ['group' => new Group(), 'users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:groups,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $group = Group::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? true),
        ]);

        $group->users()->sync($data['users'] ?? []);

        return redirect()->route('admin.groups.index')->with('success', 'Grup olusturuldu.');
    }

    public function edit(Group $group)
    {
        return view('admin.groups.form', ['group' => $group, 'users' => User::orderBy('name')->get()]);
    }

    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:groups,code,'.$group->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $group->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
        ]);

        $group->users()->sync($data['users'] ?? []);

        return redirect()->route('admin.groups.index')->with('success', 'Grup guncellendi.');
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()->route('admin.groups.index')->with('success', 'Grup silindi.');
    }
}
