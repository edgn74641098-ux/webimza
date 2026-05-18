<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Microsoft 365 Import" subtitle="Modern auth ile gelen kullanici ve gruplari secerek veritabanina aktar.">
            <x-slot name="actions">
                <form method="POST" action="{{ route('admin.microsoft365.disconnect') }}">
                    @csrf
                    <x-ui.button variant="secondary" type="submit">Baglantiyi Kapat</x-ui.button>
                </form>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error">Import yapilamadi. Lutfen secimleri ve Microsoft oturumunu kontrol edin.</x-ui.alert>
        @endif

        <form method="POST" action="{{ route('admin.microsoft365.import') }}" x-data>
            @csrf

            <div class="mb-4 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-950">Secerek import</p>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ count($users) }} kullanici ve {{ count($groups) }} grup bulundu. Grup secilirse uyeleri de gerekli bilgilerle aktarilir.
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.settings.index') }}"><x-ui.button variant="secondary" type="button">Ayarlara Don</x-ui.button></a>
                    <x-ui.button type="submit">Secilenleri Import Et</x-ui.button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950">Kullanicilar</h2>
                            <p class="text-xs text-slate-500">Departman, unvan, telefon ve adres alanlariyla import edilir.</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" class="rounded border-slate-300" @change="$el.closest('section').querySelectorAll('input[name=&quot;users[]&quot;]').forEach((input) => input.checked = $el.checked)">
                            Tumunu sec
                        </label>
                    </div>
                    <x-ui.data-table>
                        <x-slot name="head">
                            <tr>
                                <th class="w-10"></th>
                                <th>Kullanici</th>
                                <th>Departman</th>
                                <th>Durum</th>
                            </tr>
                        </x-slot>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <input type="checkbox" name="users[]" value="{{ $user['id'] }}" class="rounded border-slate-300">
                                </td>
                                <td>
                                    <div class="min-w-0">
                                        <div class="break-words font-medium text-slate-950">{{ $user['name'] ?: '-' }}</div>
                                        <div class="break-all text-xs text-slate-500">{{ $user['email'] }}</div>
                                        @if($user['title'])
                                            <div class="mt-1 break-words text-xs text-slate-500">{{ $user['title'] }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="break-words">{{ $user['department'] ?: '-' }}</td>
                                <td>
                                    <x-ui.badge :status="$user['enabled'] ? 'active' : 'inactive'">{{ $user['enabled'] ? 'aktif' : 'pasif' }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.data-table>
                </section>

                <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-950">Gruplar</h2>
                            <p class="text-xs text-slate-500">Secilen gruplar ve kullanici uyelikleri import edilir.</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" class="rounded border-slate-300" @change="$el.closest('section').querySelectorAll('input[name=&quot;groups[]&quot;]').forEach((input) => input.checked = $el.checked)">
                            Tumunu sec
                        </label>
                    </div>
                    <x-ui.data-table>
                        <x-slot name="head">
                            <tr>
                                <th class="w-10"></th>
                                <th>Grup</th>
                                <th>Kod</th>
                                <th>Tip</th>
                            </tr>
                        </x-slot>
                        @foreach($groups as $group)
                            <tr>
                                <td>
                                    <input type="checkbox" name="groups[]" value="{{ $group['id'] }}" class="rounded border-slate-300">
                                </td>
                                <td>
                                    <div class="break-words font-medium text-slate-950">{{ $group['name'] ?: '-' }}</div>
                                    @if($group['description'])
                                        <div class="mt-1 break-words text-xs text-slate-500">{{ $group['description'] }}</div>
                                    @endif
                                </td>
                                <td class="break-all font-mono text-xs">{{ $group['code'] }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @if($group['mail_enabled'])
                                            <x-ui.badge status="active">mail</x-ui.badge>
                                        @endif
                                        @if($group['security_enabled'])
                                            <x-ui.badge status="warning">security</x-ui.badge>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.data-table>
                </section>
            </div>

            <div class="sticky bottom-0 mt-4 flex flex-col gap-3 border border-slate-200 bg-white/95 px-5 py-4 shadow-sm backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-slate-500">Oturum token'i gecicidir; sifre veya Microsoft kimlik bilgisi veritabanina yazilmaz.</p>
                <x-ui.button type="submit">Secilenleri Import Et</x-ui.button>
            </div>
        </form>
    </x-ui.app-shell>
</x-app-layout>
