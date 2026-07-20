@php
    $menu = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard'],
        ['route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'icon' => 'fa-user-friends', 'label' => 'Customers'],
        ['route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'icon' => 'fa-users', 'label' => 'All Users'],
        ['route' => 'admin.vendors.index', 'pattern' => 'admin.vendors.*', 'icon' => 'fa-store', 'label' => 'Vendors'],
        ['route' => 'admin.painters.index', 'pattern' => 'admin.painters.*', 'icon' => 'fa-paint-brush', 'label' => 'Painters'],
        ['route' => 'admin.delivery-agents.index', 'pattern' => 'admin.delivery-agents.*', 'icon' => 'fa-shipping-fast', 'label' => 'Delivery Partners'],
        ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'icon' => 'fa-tags', 'label' => 'Categories'],
        ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'icon' => 'fa-fill-drip', 'label' => 'Products'],
        ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => 'fa-shopping-cart', 'label' => 'Orders'],
        ['route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*', 'icon' => 'fa-calendar-check', 'label' => 'Painter Bookings'],
        ['route' => 'admin.addresses.index', 'pattern' => 'admin.addresses.*', 'icon' => 'fa-map-marker-alt', 'label' => 'Addresses'],
        ['route' => 'admin.carts.index', 'pattern' => 'admin.carts.*', 'icon' => 'fa-shopping-basket', 'label' => 'Abandoned Carts'],
        ['route' => 'admin.payments.index', 'pattern' => 'admin.payments.*', 'icon' => 'fa-credit-card', 'label' => 'Payments'],
        ['route' => 'admin.notifications.index', 'pattern' => 'admin.notifications.*', 'icon' => 'fa-bell', 'label' => 'Notifications'],
        ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'icon' => 'fa-chart-bar', 'label' => 'Reports'],
    ];
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <span class="brand-image img-circle elevation-3 bg-white d-flex align-items-center justify-content-center" style="width:33px;height:33px;">
            <i class="fas fa-palette text-primary"></i>
        </span>
        <span class="brand-text font-weight-light">Paint Store <b>Admin</b></span>
    </a>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <span class="img-circle elevation-2 bg-light text-primary d-inline-flex align-items-center justify-content-center" style="width:34px;height:34px;font-weight:700;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
            </div>
            <div class="info">
                <a href="{{ route('admin.profile.edit') }}" class="d-block">{{ auth()->user()->name }}</a>
                <small class="text-muted">Administrator</small>
            </div>
        </div>
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach($menu as $item)
                    <li class="nav-item">
                        <a href="{{ route($item['route']) }}" class="nav-link {{ request()->routeIs($item['pattern']) ? 'active' : '' }}">
                            <i class="nav-icon fas {{ $item['icon'] }}"></i>
                            <p>{{ $item['label'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
