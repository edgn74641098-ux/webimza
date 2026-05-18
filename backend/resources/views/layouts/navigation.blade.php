@php
    $navGroups = [
        [
            'label' => 'Operasyon',
            'active' => request()->routeIs('admin.dashboard') || request()->routeIs('admin.users.*') || request()->routeIs('admin.groups.*'),
            'items' => [
                ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
                ['label' => 'Kullanicilar', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
                ['label' => 'Gruplar', 'route' => 'admin.groups.index', 'active' => request()->routeIs('admin.groups.*')],
            ],
        ],
        [
            'label' => 'Imza Yonetimi',
            'active' => request()->routeIs('admin.templates.*') || request()->routeIs('admin.assignments.*') || request()->routeIs('admin.updates.*'),
            'items' => [
                ['label' => 'Imza Sablonlari', 'route' => 'admin.templates.index', 'active' => request()->routeIs('admin.templates.*')],
                ['label' => 'Atamalar', 'route' => 'admin.assignments.index', 'active' => request()->routeIs('admin.assignments.*')],
                ['label' => 'Guncelleme Yonetimi', 'route' => 'admin.updates.index', 'active' => request()->routeIs('admin.updates.*')],
            ],
        ],
        [
            'label' => 'Add-in',
            'active' => request()->routeIs('admin.devices.*') || request()->routeIs('admin.logs.*') || request()->routeIs('admin.deployment.*'),
            'items' => [
                ['label' => 'Add-in Cihazlari', 'route' => 'admin.devices.index', 'active' => request()->routeIs('admin.devices.*')],
                ['label' => 'Loglar', 'route' => 'admin.logs.index', 'active' => request()->routeIs('admin.logs.*')],
                ['label' => 'Add-in Dagitimi', 'route' => 'admin.deployment.index', 'active' => request()->routeIs('admin.deployment.*')],
            ],
        ],
        [
            'label' => 'Sistem',
            'active' => request()->routeIs('admin.settings.*'),
            'items' => [
                ['label' => 'Ayarlar', 'route' => 'admin.settings.index', 'active' => request()->routeIs('admin.settings.*')],
            ],
        ],
    ];
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/95 backdrop-blur">
    <div class="app-container">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex min-w-0 items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="flex shrink-0 items-center gap-3">
                    <div class="grid h-9 w-9 place-items-center rounded-lg bg-blue-950 text-sm font-bold text-white">TS</div>
                    <div class="hidden leading-tight sm:block">
                        <div class="text-sm font-semibold text-slate-950">TRINOX Signature</div>
                        <div class="text-xs text-slate-500">Admin Console</div>
                    </div>
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    @foreach($navGroups as $group)
                        <x-dropdown align="left" width="64" contentClasses="bg-white p-1">
                            <x-slot name="trigger">
                                <button class="{{ $group['active'] ? 'bg-blue-50 text-blue-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }} inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition">
                                    <span>{{ $group['label'] }}</span>
                                    <svg class="h-4 w-4 text-current opacity-60" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $group['label'] }}</div>
                                @foreach($group['items'] as $item)
                                    <a href="{{ route($item['route']) }}" class="{{ $item['active'] ? 'bg-blue-50 text-blue-900' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950' }} block rounded-md px-3 py-2 text-sm font-medium">
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach
                            </x-slot>
                        </x-dropdown>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span class="grid h-7 w-7 place-items-center rounded-full bg-blue-100 text-xs font-semibold text-blue-800">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="max-w-40 truncate">{{ Auth::user()->name }}</span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-3 py-2 text-xs text-slate-500">{{ Auth::user()->email }}</div>
                        <x-dropdown-link :href="route('profile.edit')">Profil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Cikis Yap</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md border border-slate-200 p-2 text-slate-700 hover:bg-slate-50 lg:hidden">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="open = false" x-cloak></div>

    <aside
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-80 max-w-[88vw] overflow-y-auto border-r border-slate-200 bg-white shadow-xl lg:hidden"
        x-cloak
    >
        <div class="border-b border-slate-200 p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="grid h-9 w-9 place-items-center rounded-lg bg-blue-950 text-sm font-bold text-white">TS</div>
                    <div>
                        <div class="text-sm font-semibold text-slate-950">TRINOX Signature</div>
                        <div class="text-xs text-slate-500">Admin Console</div>
                    </div>
                </div>
                <button @click="open = false" class="rounded-md p-2 text-slate-600 hover:bg-slate-100">Kapat</button>
            </div>
        </div>

        <div class="space-y-5 p-4">
            @foreach($navGroups as $group)
                <section>
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $group['label'] }}</div>
                    <div class="space-y-1">
                        @foreach($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="{{ $item['active'] ? 'bg-blue-50 text-blue-900' : 'text-slate-700 hover:bg-slate-50 hover:text-slate-950' }} block rounded-md px-3 py-2 text-sm font-medium">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="border-t border-slate-200 p-4">
            <div class="mb-3 text-sm font-medium text-slate-950">{{ Auth::user()->name }}</div>
            <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="mt-1 block w-full rounded-md px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Cikis Yap</button>
            </form>
        </div>
    </aside>
</nav>
