<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Atamalar" subtitle="Imza atama kurallarini sade sekilde yonetin." />
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card title="Imza Secim Onceligi">
            <x-ui.alert type="info">
                1) Kullaniciya ozel atama, 2) Grup atamasi, 3) Departman atamasi, 4) Varsayilan atama.
            </x-ui.alert>
        </x-ui.card>

        <x-ui.card title="Yeni Atama">
            <form method="POST" action="{{ route('admin.assignments.store') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-2" x-data="{ scope: '{{ old('assignment_type', 'user') }}' }">
                @csrf

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Kapsam sec</label>
                    <x-ui.select name="assignment_type" x-model="scope" required>
                        <option value="user" @selected(old('assignment_type', 'user') === 'user')>user</option>
                        <option value="group" @selected(old('assignment_type') === 'group')>group</option>
                        <option value="department" @selected(old('assignment_type') === 'department')>department</option>
                        <option value="default" @selected(old('assignment_type') === 'default')>default</option>
                    </x-ui.select>
                </div>

                <div x-show="scope === 'user'" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hedef sec (Kullanici)</label>
                    <x-ui.select name="user_id">
                        <option value="">Kullanici secin</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div x-show="scope === 'group'" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hedef sec (Grup)</label>
                    <x-ui.select name="group_id">
                        <option value="">Grup secin</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div x-show="scope === 'department'" x-cloak>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Hedef sec (Departman)</label>
                    <x-ui.select name="department_id">
                        <option value="">Departman secin</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div x-show="scope === 'default'" x-cloak class="lg:col-span-2">
                    <x-ui.alert type="info">Varsayilan kapsam secildiginde kural tum aktif kullanicilara uygulanir.</x-ui.alert>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Sablon sec</label>
                    <x-ui.select name="template_id" required>
                        <option value="">Sablon secin</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected(old('template_id') == $template->id)>{{ $template->name }} ({{ $template->version }})</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Oncelik</label>
                    <x-ui.input type="number" name="priority" min="1" :value="old('priority', 100)" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Baslangic tarihi</label>
                    <x-ui.input type="datetime-local" name="starts_at" :value="old('starts_at')" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Bitis tarihi</label>
                    <x-ui.input type="datetime-local" name="ends_at" :value="old('ends_at')" />
                </div>

                <div class="lg:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                        Aktif
                    </label>
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <x-ui.button type="submit">Atamayi Kaydet</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Atama Listesi">
            @if($assignments->isEmpty())
                <x-ui.empty-state title="Henuz atama yok" description="Ilk atamayi olusturarak hangi imzanin kime gidecegini belirleyin." />
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Oncelik</th>
                            <th>Kapsam</th>
                            <th>Hedef</th>
                            <th>Sablon</th>
                            <th>Aktif/Pasif</th>
                            <th>Baslangic</th>
                            <th>Bitis</th>
                            <th>Aksiyonlar</th>
                        </tr>
                    </x-slot>

                    @foreach($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->priority }}</td>
                            <td>{{ $assignment->assignment_type }}</td>
                            <td>{{ $assignment->target_label }}</td>
                            <td>{{ $assignment->template?->name ?? '-' }}</td>
                            <td><x-ui.badge :status="$assignment->is_active ? 'active' : 'inactive'">{{ $assignment->is_active ? 'Aktif' : 'Pasif' }}</x-ui.badge></td>
                            <td>{{ optional($assignment->starts_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ optional($assignment->ends_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" onsubmit="return confirm('Atama silinsin mi?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="secondary">Sil</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    <x-slot name="footer">
                        {{ $assignments->links() }}
                    </x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
