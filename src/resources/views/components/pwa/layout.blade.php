<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <meta name="mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="theme-color" content="#f59e0b"/>
    <title>{{ $title ?? 'Falaya SFA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
    <style>
        :root {
            --pwa-navbar-h: 64px;
            --amber:  #f59e0b;
            --amber-dark: #d97706;
            --amber-light: #fffbeb;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --emerald: #10b981;
            --emerald-light: #ecfdf5;
            --red: #ef4444;
            --red-light: #fef2f2;
        }
        body {
            background: var(--slate-100);
            font-family: 'Inter', sans-serif;
            padding-bottom: 72px;
            color: var(--slate-900);
        }
        .falaya-card {
            border-radius: 14px;
            border: 1px solid var(--slate-200);
            margin-bottom: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
        }
        .falaya-card .card-body { padding: 16px; }
        .falaya-card__title { font-weight: 600; font-size: 1rem; margin-bottom: 2px; }
        .falaya-card__subtitle { color: var(--slate-500); font-size: 0.875rem; }
        .falaya-card--danger  { border-color: var(--red);     background: var(--red-light); }
        .falaya-card--warning { border-color: var(--amber);   background: var(--amber-light); }
        .falaya-card--success { border-color: var(--emerald); background: var(--emerald-light); }
        .btn { min-height: 44px; font-weight: 500; }
        .btn-amber {
            background: var(--amber);
            border-color: var(--amber);
            color: var(--slate-900);
            font-weight: 600;
        }
        .btn-amber:hover { background: var(--amber-dark); border-color: var(--amber-dark); color: var(--slate-900); }
        .btn-outline-amber {
            border-color: var(--amber);
            color: var(--amber-dark);
            background: transparent;
        }
        .btn-outline-amber:hover { background: var(--amber-light); }
        .pwa-header {
            background: var(--slate-900);
            color: #fff;
            padding: 16px;
            margin-bottom: 16px;
        }
        .pwa-header h5 { margin: 0; font-weight: 600; }
        .pwa-header small { opacity: 0.7; font-size: 0.8rem; }
        .pwa-header .accent { color: var(--amber); }
        .pwa-navbar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: #fff;
            border-top: 1px solid var(--slate-200);
            display: flex;
            z-index: 100;
            box-shadow: 0 -1px 8px rgba(0,0,0,.08);
        }
        .pwa-navbar a {
            flex: 1; text-align: center;
            padding: 10px 4px 8px;
            text-decoration: none;
            color: var(--slate-500);
            font-size: 0.7rem;
            font-weight: 500;
            transition: color .15s;
        }
        .pwa-navbar a.active { color: var(--amber-dark); }
        .pwa-navbar a.active svg { stroke: var(--amber-dark); }
        .pwa-navbar svg {
            display: block;
            margin: 0 auto 3px;
            width: 22px; height: 22px;
            stroke: var(--slate-500);
            fill: none;
            stroke-width: 1.7;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: stroke .15s;
        }
        .progress { height: 8px; border-radius: 4px; }
        .progress-bar-amber { background: var(--amber); }
        .badge-status-planned     { background: var(--slate-200); color: var(--slate-700); }
        .badge-status-completed   { background: var(--emerald-light); color: #047857; }
        .badge-status-no_order    { background: var(--amber-light); color: #b45309; }
        .badge-status-outlet_closed { background: var(--amber-light); color: #b45309; }
        .badge-status-in_progress { background: var(--amber-light); color: #b45309; }
        .badge-status-skipped     { background: var(--red-light); color: #991b1b; }
    </style>
    @vite('resources/css/pwa.css')
    @livewireStyles
</head>
<body>
    {{ $slot }}
    <nav class="pwa-navbar">
        <a href="/pwa/dashboard" class="{{ request()->is('pwa/dashboard') ? 'active' : '' }}">
            <x-heroicon-o-home />
            Beranda
        </a>
        <a href="/pwa/visits" class="{{ request()->is('pwa/visits*') ? 'active' : '' }}">
            <x-heroicon-o-map-pin />
            Kunjungan
        </a>
        <a href="/pwa/stock" class="{{ request()->is('pwa/stock*') ? 'active' : '' }}">
            <x-heroicon-o-cube />
            Stok
        </a>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('order-success', () => {
                setTimeout(() => window.location.reload(), 1500);
            });
            Livewire.on('payment-success', () => {
                const parts = window.location.pathname.split('/');
                const visitPlanId = parts[3] ?? null;
                setTimeout(() => {
                    window.location.href = visitPlanId ? '/pwa/visits/' + visitPlanId : '/pwa/visits';
                }, 1500);
            });
        });
    </script>
    @livewireScripts
</body>
</html>
