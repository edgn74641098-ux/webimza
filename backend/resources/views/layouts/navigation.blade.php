<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/90 backdrop-blur">
    <div class="app-container">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-lg bg-blue-900"></div>
                    <span class="hidden text-sm font-semibold text-slate-800 sm:inline">TRINOX Signature Manager</span>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-nav-link>
                    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Kullanicilar</x-nav-link>
                    <x-nav-link :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">Imza Sablonlari</x-nav-link>
                    <x-nav-link :href="route('admin.assignments.index')" :active="request()->routeIs('admin.assignments.*')">Atamalar</x-nav-link>
                    <x-nav-link :href="route('admin.updates.index')" :active="request()->routeIs('admin.updates.*')">Guncelleme Yonetimi</x-nav-link>
                    <x-nav-link :href="route('admin.devices.index')" :active="request()->routeIs('admin.devices.*')">Add-in Cihazlari</x-nav-link>
                    <x-nav-link :href="route('admin.logs.index')" :active="request()->routeIs('admin.logs.*')">Loglar</x-nav-link>
                    <x-nav-link :href="route('admin.deployment.index')" :active="request()->routeIs('admin.deployment.*')">Add-in Dagitimi</x-nav-link>
                    <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">Ayarlar</x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span class="h-6 w-6 rounded-full bg-blue-100 text-center text-xs leading-6 text-blue-700">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span>{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Cikis Yap</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
        @click="open = false"
        x-cloak
    ></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white p-4 shadow-xl lg:hidden"
        x-cloak
    >
        <div class="mb-4 flex items-center justify-between">
            <span class="text-sm font-semibold text-slate-800">Menu</span>
            <button @click="open = false" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100">Kapat</button>
        </div>
        <div class="space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">Dashboard</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Kullanicilar</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">Imza Sablonlari</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.assignments.index')" :active="request()->routeIs('admin.assignments.*')">Atamalar</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.updates.index')" :active="request()->routeIs('admin.updates.*')">Guncelleme Yonetimi</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.devices.index')" :active="request()->routeIs('admin.devices.*')">Add-in Cihazlari</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.logs.index')" :active="request()->routeIs('admin.logs.*')">Loglar</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.deployment.index')" :active="request()->routeIs('admin.deployment.*')">Add-in Dagitimi</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">Ayarlar</x-responsive-nav-link>
        </div>
    </aside>
</nav>
