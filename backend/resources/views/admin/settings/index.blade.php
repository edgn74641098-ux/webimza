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
        <x-ui.card title="Operasyon Kisa Yollari" subtitle="Sik kullanilan yonetim ekranlarina hizli gecis yapin.">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('admin.deployment.index') }}"><x-ui.button class="w-full">Add-in Dagitimi</x-ui.button></a>
                <a href="{{ route('admin.templates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Sablonlar</x-ui.button></a>
                <a href="{{ route('admin.assignments.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Atamalar</x-ui.button></a>
                <a href="{{ route('admin.groups.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Gruplar</x-ui.button></a>
                <a href="{{ route('admin.updates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Guncelleme Yonetimi</x-ui.button></a>
                <a href="{{ route('admin.logs.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Loglar</x-ui.button></a>
            </div>
        </x-ui.card>

        <x-ui.card title="Sistem Durumu">
            <x-ui.alert type="info">
                Bu ekran temel operasyon akislarini birlestirir. Ayrintili global ayarlar sonraki surumde bu alana eklenecek.
            </x-ui.alert>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>

