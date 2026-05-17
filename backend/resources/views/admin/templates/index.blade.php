<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Imza Sablonlari" subtitle="Sablonlari yonetin, versiyonlayin ve duzenleyin.">
            <x-slot name="actions">
                <a href="{{ route('admin.templates.create') }}"><x-ui.button>Yeni Sablon Olustur</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.templates.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <x-ui.input name="q" :value="$filters['q'] ?? ''" placeholder="Sablon adi, tip veya versiyon" />
                <x-ui.select name="type">
                    <option value="">Tum tipler</option>
                    <option value="both" @selected(($filters['type'] ?? '') === 'both')>both</option>
                    <option value="new_message" @selected(($filters['type'] ?? '') === 'new_message')>new_message</option>
                    <option value="reply" @selected(($filters['type'] ?? '') === 'reply')>reply</option>
                </x-ui.select>
                <x-ui.select name="status">
                    <option value="">Tum durumlar</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
                </x-ui.select>
                <div class="flex items-center gap-2">
                    <x-ui.button type="submit">Filtrele</x-ui.button>
                    <a href="{{ route('admin.templates.index') }}"><x-ui.button variant="secondary" type="button">Temizle</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($templates->isEmpty())
                <x-ui.empty-state title="Henuz imza sablonu olusturulmadi." description="Ilk kurumsal imza sablonunuzu olusturarak baslayin.">
                    <x-slot name="action">
                        <a href="{{ route('admin.templates.create') }}"><x-ui.button>Sablon Olustur</x-ui.button></a>
                    </x-slot>
                </x-ui.empty-state>
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Sablon adi</th>
                            <th>Tip</th>
                            <th>Versiyon</th>
                            <th>Aktif/Pasif</th>
                            <th>Son guncelleme</th>
                            <th class="text-right">Aksiyonlar</th>
                        </tr>
                    </x-slot>
                    @foreach($templates as $template)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $template->name }}</td>
                            <td>{{ $template->type }}</td>
                            <td>{{ $template->version }}</td>
                            <td><x-ui.badge :status="$template->is_active ? 'active' : 'inactive'">{{ $template->is_active ? 'Aktif' : 'Pasif' }}</x-ui.badge></td>
                            <td>{{ optional($template->updated_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.templates.edit', $template) }}"><x-ui.button variant="secondary" type="button">Duzenle</x-ui.button></a>
                            </td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $templates->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
