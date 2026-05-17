<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Ayarlar" subtitle="Sistem ve operasyon ayarlari.">
            <x-slot name="actions">
                <a href="{{ route('admin.deployment.index') }}"><x-ui.button>Add-in Dagitimi</x-ui.button></a>
                <a href="{{ route('admin.dashboard') }}"><x-ui.button variant="secondary" type="button">Dashboard</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>
    <x-ui.app-shell>
        <x-ui.card>
            <x-ui.empty-state title="Ayarlar ekrani" description="Bu adimda sadece route/menu sadeleştirmesi yapildi. Ayar modulleri sonraki fazda acilacak." />
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>

