<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <meta name="mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="theme-color" content="#0054a6"/>
    <title>{{ $title ?? 'Falaya SFA' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
    <style>
        body { background: #f4f6fb; font-family: 'Inter', sans-serif; padding-bottom: 72px; }
        .falaya-card { border-radius: 14px; border: 1px solid #e5e9f0; margin-bottom: 12px; background: #fff; }
        .falaya-card .card-body { padding: 16px; }
        .falaya-card__title { font-weight: 600; font-size: 1rem; margin-bottom: 2px; }
        .falaya-card__subtitle { color: #6c757d; font-size: 0.875rem; }
        .falaya-card--danger { border-color: #dc3545; background: #fff5f5; }
        .falaya-card--warning { border-color: #ffc107; background: #fffdf0; }
        .falaya-card--success { border-color: #28a745; background: #f0fff4; }
        .btn { min-height: 44px; }
        .progress { height: 8px; border-radius: 4px; }
        .pwa-header { background: #0054a6; color: #fff; padding: 16px; margin-bottom: 16px; }
        .pwa-header h5 { margin: 0; font-weight: 600; }
        .pwa-header small { opacity: 0.8; font-size: 0.8rem; }
        .pwa-navbar { position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            border-top: 1px solid #e5e9f0; display: flex; z-index: 100; }
        .pwa-navbar a { flex: 1; text-align: center; padding: 10px 4px 8px;
            text-decoration: none; color: #6c757d; font-size: 0.7rem; }
        .pwa-navbar a.active { color: #0054a6; }
        .pwa-navbar .nav-icon { font-size: 1.3rem; display: block; }
        .badge-status-planned { background: #e9ecef; color: #495057; }
        .badge-status-completed { background: #d1e7dd; color: #0f5132; }
        .badge-status-no_order { background: #fff3cd; color: #664d03; }
        .badge-status-outlet_closed { background: #cfe2ff; color: #084298; }
        .badge-status-in_progress { background: #fff3cd; color: #664d03; }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    <nav class="pwa-navbar">
        <a href="/pwa/dashboard" class="{{ request()->is('pwa/dashboard') ? 'active' : '' }}">
            <span class="nav-icon">🏠</span>Beranda
        </a>
        <a href="/pwa/visits" class="{{ request()->is('pwa/visits*') ? 'active' : '' }}">
            <span class="nav-icon">📍</span>Kunjungan
        </a>
        <a href="/pwa/stock" class="{{ request()->is('pwa/stock*') ? 'active' : '' }}">
            <span class="nav-icon">📦</span>Stok
        </a>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
