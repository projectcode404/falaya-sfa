<!doctype html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>{{ $title ?? 'Falaya SFA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-full">

    {{-- ── SIDEBAR OVERLAY (mobile) ──────────────────────── --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-slate-900/60 md:hidden"
        @click="sidebarOpen = false"
        style="display:none"
    ></div>

    {{-- ── SIDEBAR ────────────────────────────────────────── --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-56 flex-col bg-slate-900 transition-transform duration-200 ease-in-out md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        {{-- Brand --}}
        <div class="flex h-14 items-center gap-3 border-b border-slate-800 px-4 flex-shrink-0">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-sm font-bold text-slate-900 flex-shrink-0">F</div>
            <div>
                <div class="text-sm font-bold leading-tight text-white">Falaya SFA</div>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-amber-400">
                    {{ Auth::user()->role ?? Auth::user()->getRoleNames()->first() }}
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

            @if(Auth::user()->hasRole('OWNER'))
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Utama</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/owner/dashboard"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('owner/dashboard') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-squares-2x2 class="h-4 w-4 flex-shrink-0"/>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/owner/approvals"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('owner/approvals*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-clipboard-document-check class="h-4 w-4 flex-shrink-0"/>
                            Approval
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Laporan</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/reports/sales"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/sales') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-chart-bar class="h-4 w-4 flex-shrink-0"/>
                            Penjualan
                        </a>
                    </li>
                    <li>
                        <a href="/reports/stock"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/stock') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-cube class="h-4 w-4 flex-shrink-0"/>
                            Stok
                        </a>
                    </li>
                    <li>
                        <a href="/reports/ar"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/ar') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-banknotes class="h-4 w-4 flex-shrink-0"/>
                            AR Outstanding
                        </a>
                    </li>
                    <li>
                        <a href="/reports/visits"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/visits') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-map-pin class="h-4 w-4 flex-shrink-0"/>
                            Kunjungan
                        </a>
                    </li>
                    <li>
                        <a href="/reports/collection-risk"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/collection-risk') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-exclamation-triangle class="h-4 w-4 flex-shrink-0"/>
                            Collection Risk
                        </a>
                    </li>
                    <li>
                        <a href="/reports/bad-stock"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports/bad-stock') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-shield-exclamation class="h-4 w-4 flex-shrink-0"/>
                            Bad Stock
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Sistem</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/settings"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('settings*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 flex-shrink-0"/>
                            Settings
                        </a>
                    </li>
                </ul>
            </div>
            @endif

            @if(Auth::user()->hasRole('ADMIN'))
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Operasional</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/admin/dashboard"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/dashboard') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-squares-2x2 class="h-4 w-4 flex-shrink-0"/>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="/admin/stock-loading"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/stock-loading*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-arrow-up-tray class="h-4 w-4 flex-shrink-0"/>
                            Stock Loading
                        </a>
                    </li>
                    <li>
                        <a href="/admin/stock-unloading"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/stock-unloading*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-arrow-down-tray class="h-4 w-4 flex-shrink-0"/>
                            Stock Unloading
                        </a>
                    </li>
                    <li>
                        <a href="/admin/cash-reconciliation"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/cash-reconciliation*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-arrows-right-left class="h-4 w-4 flex-shrink-0"/>
                            Rekonsiliasi
                        </a>
                    </li>
                    <li>
                        <a href="/admin/payment-transfer"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/payment-transfer*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-credit-card class="h-4 w-4 flex-shrink-0"/>
                            Payment Transfer
                        </a>
                    </li>
                    <li>
                        <a href="/admin/closing"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/closing*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-flag class="h-4 w-4 flex-shrink-0"/>
                            Tutup Hari
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Master Data</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/admin/products"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/products*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-tag class="h-4 w-4 flex-shrink-0"/>
                            Produk
                        </a>
                    </li>
                    <li>
                        <a href="/admin/customers"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/customers*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-building-storefront class="h-4 w-4 flex-shrink-0"/>
                            Customer
                        </a>
                    </li>
                    <li>
                        <a href="/admin/areas"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/areas*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-map class="h-4 w-4 flex-shrink-0"/>
                            Area
                        </a>
                    </li>
                    <li>
                        <a href="/admin/users"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/users*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-users class="h-4 w-4 flex-shrink-0"/>
                            User
                        </a>
                    </li>
                    <li>
                        <a href="/admin/visit-schedules"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('admin/visit-schedules*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-calendar-days class="h-4 w-4 flex-shrink-0"/>
                            Jadwal Kunjungan
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <p class="mb-1 px-2 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Laporan</p>
                <ul class="space-y-0.5">
                    <li>
                        <a href="/reports/sales"
                           class="flex items-center gap-2.5 rounded-lg px-2 py-2 text-sm font-medium transition-colors
                                  {{ request()->is('reports*') ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <x-heroicon-o-chart-bar class="h-4 w-4 flex-shrink-0"/>
                            Laporan
                        </a>
                    </li>
                </ul>
            </div>
            @endif

        </nav>

        {{-- User footer --}}
        <div class="border-t border-slate-800 p-3">
            <div class="flex items-center gap-2.5 rounded-lg px-2 py-2">
                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold text-slate-300">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-white">{{ Auth::user()->name }}</div>
                    <div class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors" title="Keluar">
                        <x-heroicon-o-arrow-right-on-rectangle class="h-4 w-4"/>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── MAIN ────────────────────────────────────────────── --}}
    <div class="flex min-h-full flex-1 flex-col md:pl-56">

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-slate-200 bg-white px-4 md:px-6">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 md:hidden">
                <x-heroicon-o-bars-3 class="h-5 w-5"/>
            </button>

            <div>
                <h1 class="text-sm font-semibold text-slate-900">{{ $heading ?? ($title ?? '') }}</h1>
            </div>

            <div class="ml-auto flex items-center gap-3">
                {{-- Hari operasional chip --}}
                @php
                    try {
                        $opsDate = app(\App\DomainServices\OperationalDateService::class)->current();
                    } catch (\Throwable $e) {
                        $opsDate = null;
                    }
                @endphp
                @if($opsDate)
                <div class="flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Ops: {{ $opsDate->translatedFormat('d M Y') }}
                </div>
                @endif
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-4 mt-4 md:mx-6 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <x-heroicon-o-check-circle class="h-4 w-4 flex-shrink-0 text-emerald-500"/>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mx-4 mt-4 md:mx-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-heroicon-o-x-circle class="h-4 w-4 flex-shrink-0 text-red-500"/>
            {{ session('error') }}
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 md:p-6">
            {{ $slot }}
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
