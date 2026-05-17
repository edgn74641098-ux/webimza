<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Gruplar" subtitle="Kullanici segmentlerini yonetin, atama kapsamlarini kontrol edin.">
            <x-slot name="actions">
                <a href="{{ route('admin.groups.create') }}"><x-ui.button>+ Yeni Grup</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        @if(session('success'))<x-ui.alert type="success">{{ session('success') }}</x-ui.alert>@endif

        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <x-ui.stat-card label="Toplam Grup" :value="$stats['total']" />
            <x-ui.stat-card label="Aktif Grup" :value="$stats['active']" />
            <x-ui.stat-card label="Toplam Uye Kaydi" :value="$stats['members']" />
        </div>

        <x-ui.card>
            <form class="grid grid-cols-1 gap-2 md:grid-cols-4">
                <x-ui.input name="q" :value="$filters['q'] ?? ''" placeholder="Grup adi, kod veya aciklama" />
                <x-ui.select name="status">
                    <option value="">Durum</option>
                    <option value="active" @selected(($filters['status'] ?? '')==='active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '')==='inactive')>Pasif</option>
                </x-ui.select>
                <x-ui.button type="submit">Filtrele</x-ui.button>
                <a href="{{ route('admin.groups.index') }}"><x-ui.button variant="secondary" type="button">Temizle</x-ui.button></a>
            </form>
        </x-ui.card>

        <x-ui.card title="Grup Listesi" subtitle="Atama kurallari icin kullanilabilir gruplar">
            @if($groups->isEmpty())
                <x-ui.empty-state title="Henuz grup olusturulmadi." description="Kullanicilari segmentlemek icin ilk grubu olusturun.">
                    <x-slot name="action">
                        <a href="{{ route('admin.groups.create') }}"><x-ui.button>Grup Olustur</x-ui.button></a>
                    </x-slot>
                </x-ui.empty-state>
            @else
                <x-ui.table>
                    <x-slot name="head"><tr><th>Grup</th><th>Kod</th><th>Uye</th><th>Durum</th><th>Aksiyon</th></tr></x-slot>
                    @foreach($groups as $group)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $group->name }}</td>
                            <td>{{ $group->code ?: '-' }}</td>
                            <td>{{ $group->users_count }}</td>
                            <td><x-ui.badge :status="$group->is_active ? 'active' : 'inactive'">{{ $group->is_active ? 'Aktif' : 'Pasif' }}</x-ui.badge></td>
                            <td>
                                <x-ui.dropdown>
                                    <x-slot name="trigger"><x-ui.button variant="secondary" type="button">Aksiyon</x-ui.button></x-slot>
                                    <a href="{{ route('admin.groups.edit', $group) }}" class="block rounded px-3 py-2 text-sm hover:bg-slate-100">Duzenle</a>
                                    <a href="{{ route('admin.assignments.index') }}" class="block rounded px-3 py-2 text-sm hover:bg-slate-100">Atama ekranina git</a>
                                    <form method="POST" action="{{ route('admin.groups.destroy', $group) }}">@csrf @method('DELETE')<button class="block w-full rounded px-3 py-2 text-left text-sm text-rose-700 hover:bg-rose-50">Sil</button></form>
                                </x-ui.dropdown>
                            </td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $groups->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>

