@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>₹{{ number_format($stats['revenue_month'], 0) }}</h3>
                <p>Revenue This Month</p>
            </div>
            <div class="icon"><i class="fas fa-rupee-sign"></i></div>
            <a href="{{ route('admin.payments.index') }}" class="small-box-footer">Today: ₹{{ number_format($stats['revenue_today'], 0) }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['orders_month'] }}</h3>
                <p>Orders This Month</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="{{ route('admin.orders.index') }}" class="small-box-footer">{{ $stats['orders_today'] }} today <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['awaiting_packing'] }}</h3>
                <p>Awaiting Packing</p>
            </div>
            <div class="icon"><i class="fas fa-box-open"></i></div>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="small-box-footer">{{ $stats['active_orders'] }} active <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['vendors'] }}</h3>
                <p>Active Vendors</p>
            </div>
            <div class="icon"><i class="fas fa-store"></i></div>
            <a href="{{ route('admin.vendors.index') }}" class="small-box-footer">{{ $stats['delivery_agents'] }} delivery partners <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Customers</span>
                <span class="info-box-number">{{ $stats['customers'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-paint-brush"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Painters</span>
                <span class="info-box-number">{{ $stats['painters'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-fill-drip"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Products</span>
                <span class="info-box-number">{{ $stats['products'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Delivered</span>
                <span class="info-box-number">{{ $stats['delivered_orders'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-credit-card"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Online / COD</span>
                <span class="info-box-number">{{ $stats['online_orders'] }} / {{ $stats['cod_orders'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6">
        <div class="info-box mb-3">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-calendar-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Active Bookings</span>
                <span class="info-box-number">{{ $stats['active_bookings'] }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Revenue — Last 7 Days</h3>
                <div class="card-tools">
                    <span class="badge badge-primary">All-time: ₹{{ number_format($stats['revenue_total'], 0) }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-wrap"><canvas id="revenueChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Orders — 7 Days</h3></div>
            <div class="card-body"><div class="chart-wrap"><canvas id="ordersChart"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title">Order Status</h3></div>
            <div class="card-body"><div class="chart-wrap chart-wrap-donut"><canvas id="statusChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-0"><h3 class="card-title">Payment Methods</h3></div>
            <div class="card-body"><div class="chart-wrap chart-wrap-donut"><canvas id="paymentChart"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy mr-1 text-warning"></i> Top Vendors</h3>
                <div class="card-tools"><a href="{{ route('admin.vendors.index') }}" class="btn btn-tool btn-sm">View all</a></div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead><tr><th>#</th><th>Vendor</th><th>Orders</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody>
                        @forelse($topVendors as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    @if($row->vendor)
                                        <a href="{{ route('admin.vendors.show', $row->vendor) }}">{{ $row->vendor->business_name ?? $row->vendor->name }}</a>
                                    @else
                                        Unknown
                                    @endif
                                </td>
                                <td>{{ $row->order_count }}</td>
                                <td class="text-right text-success font-weight-bold">₹{{ number_format($row->revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No vendor orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart mr-1"></i> Recent Orders</h3>
                <div class="card-tools"><a href="{{ route('admin.orders.index') }}" class="btn btn-tool btn-sm">View all</a></div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Time</th></tr></thead>
                    <tbody>
                        @forelse($recent_orders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer->name }}</td>
                                <td>₹{{ number_format($order->total_amount, 0) }}</td>
                                <td><span class="badge badge-blue">{{ $order->order_status->label() }}</span></td>
                                <td class="text-muted text-sm">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-calendar-check mr-1"></i> Recent Painter Bookings</h3>
        <div class="card-tools"><a href="{{ route('admin.bookings.index') }}" class="btn btn-tool btn-sm">View all</a></div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped">
            <thead><tr><th>Booking #</th><th>Customer</th><th>Date</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @forelse($recent_bookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_number }}</td>
                        <td>{{ $booking->customer->name }}</td>
                        <td>{{ $booking->booking_date->format('d M Y') }}</td>
                        <td><span class="badge badge-yellow">{{ $booking->status->label() }}</span></td>
                                <td><a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-xs btn-default">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const chartData = @json($chartData);
    const colors = { primary: '#3c8dbc', success: '#28a745', warning: '#ffc107', danger: '#dc3545', info: '#17a2b8', purple: '#6f42c1' };
    const palette = [colors.primary, colors.success, colors.warning, colors.info, colors.purple, colors.danger];
    const defaults = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: chartData.revenue.labels,
            datasets: [{ label: 'Revenue', data: chartData.revenue.data, borderColor: colors.primary, backgroundColor: 'rgba(60,141,188,.15)', fill: true, tension: 0.4 }]
        },
        options: { ...defaults, scales: { y: { beginAtZero: true, ticks: { callback: v => '₹' + v } } } }
    });

    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: { labels: chartData.orders.labels, datasets: [{ data: chartData.orders.data, backgroundColor: colors.success, borderRadius: 4 }] },
        options: { ...defaults, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    function donut(id, labels, data) {
        if (!labels.length) { document.getElementById(id).parentElement.innerHTML = '<p class="empty-hint">No data yet</p>'; return; }
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: palette, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
    donut('statusChart', chartData.orderStatus.labels, chartData.orderStatus.data);
    donut('paymentChart', chartData.paymentMethods.labels, chartData.paymentMethods.data);
})();
</script>
@endpush
