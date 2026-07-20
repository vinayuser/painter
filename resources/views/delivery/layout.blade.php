<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Delivery') - Paint Store</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin-custom.css') }}">
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="wrapper">
    <aside class="sidebar sidebar-delivery" id="sidebar" style="background:#1e3a5f;">
        <div class="sidebar-brand">
            <h2>🚚 Delivery Panel</h2>
            <span>{{ auth()->user()->name }}</span>
            <button type="button" class="sidebar-close" id="sidebarClose" aria-label="Close menu">✕</button>
        </div>
        <nav>
            <a href="{{ route('delivery.dashboard') }}" class="{{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span> Dashboard
            </a>
            <a href="{{ route('delivery.orders.index') }}" class="{{ request()->routeIs('delivery.orders.index') && request('filter') !== 'past' ? 'active' : '' }}">
                <span class="icon">📦</span> Active Deliveries
            </a>
            <a href="{{ route('delivery.orders.index', ['filter' => 'past']) }}" class="{{ request('filter') === 'past' ? 'active' : '' }}">
                <span class="icon">📋</span> Past Deliveries
            </a>
            @php $deliveryUnread = \App\Models\AppNotification::where('user_id', auth()->id())->whereNull('read_at')->count(); @endphp
            <a href="{{ route('delivery.notifications.index') }}" class="{{ request()->routeIs('delivery.notifications.*') ? 'active' : '' }}">
                <span class="icon">🔔</span> Notifications
                @if($deliveryUnread > 0)<span class="badge" style="background:#dc2626;color:#fff;border-radius:999px;padding:1px 6px;font-size:11px;margin-left:4px;">{{ $deliveryUnread }}</span>@endif
            </a>
        </nav>
    </aside>
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle" id="menuToggle" aria-label="Open menu">☰</button>
                <span class="topbar-title">@yield('title', 'Dashboard')</span>
            </div>
            <div class="topbar-user">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="name">{{ auth()->user()->name }}</span>
                <form action="{{ route('delivery.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm">Logout</button>
                </form>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">✕ {{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
<script>
(function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('menuToggle');
    const closeBtn = document.getElementById('sidebarClose');
    function open() { sidebar.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
    function close() { sidebar.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }
    openBtn?.addEventListener('click', open);
    closeBtn?.addEventListener('click', close);
    overlay?.addEventListener('click', close);
    sidebar?.querySelectorAll('nav a').forEach(a => a.addEventListener('click', () => window.innerWidth < 900 && close()));
})();
</script>
@stack('scripts')
</body>
</html>
