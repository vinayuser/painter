@extends('vendor.layout')

@section('title', 'Dashboard')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-title">Vendor Dashboard</h1>
        <p class="dash-subtitle">{{ auth()->user()->business_name ?? auth()->user()->name }} · {{ now()->format('M j, Y') }}</p>
    </div>
    <div class="dash-actions">
        <button type="button" id="enable-web-push" class="btn btn-secondary btn-sm">Enable Order Alerts</button>
        <span id="push-status" class="text-muted" style="font-size:12px;margin-left:8px;"></span>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm">+ Add Product</a>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
    <div class="kpi-card kpi-success">
        <div class="kpi-icon">💰</div>
        <div class="kpi-body">
            <span class="kpi-value">₹{{ number_format($revenueMonth, 0) }}</span>
            <span class="kpi-label">Revenue this month</span>
        </div>
    </div>
    <div class="kpi-card kpi-primary">
        <div class="kpi-icon">🛢️</div>
        <div class="kpi-body">
            <span class="kpi-value">{{ $productCount }}</span>
            <span class="kpi-label">Products listed</span>
        </div>
    </div>
    <div class="kpi-card kpi-warning">
        <div class="kpi-icon">⏳</div>
        <div class="kpi-body">
            <span class="kpi-value">{{ $pendingPacking }}</span>
            <span class="kpi-label">Awaiting packing</span>
            <span class="kpi-meta">{{ $activeOrders }} active</span>
        </div>
    </div>
    <div class="kpi-card kpi-info">
        <div class="kpi-icon">✅</div>
        <div class="kpi-body">
            <span class="kpi-value">{{ $completedOrders }}</span>
            <span class="kpi-label">Completed orders</span>
        </div>
    </div>
</div>

<div class="chart-grid" style="grid-template-columns:1.5fr 1fr;">
    <div class="card chart-card">
        <div class="card-head"><h3>Orders — Last 7 days</h3></div>
        <div class="chart-wrap"><canvas id="vendorOrdersChart"></canvas></div>
    </div>
    <div class="card chart-card">
        <div class="card-head"><h3>Payment split</h3></div>
        <div class="chart-wrap chart-wrap-donut"><canvas id="vendorPaymentChart"></canvas></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <h3>Recent orders</h3>
        <a href="{{ route('vendor.orders.index') }}" class="link-sm">View all</a>
    </div>
    <div class="order-feed">
        @forelse($recentOrders as $order)
            <a href="{{ route('vendor.orders.show', $order) }}" class="order-feed-item">
                <div class="order-feed-top">
                    <strong>{{ $order->order_number }}</strong>
                    <span class="badge {{ $order->vendor_packing_status->value === 'packed' ? 'badge-green' : 'badge-yellow' }}">{{ $order->vendor_packing_status->label() }}</span>
                </div>
                <div class="order-feed-meta">{{ $order->customer->name }}</div>
                <div class="order-feed-bottom">
                    <span class="order-amount">₹{{ number_format($order->total_amount, 0) }}</span>
                    <span class="order-time">{{ $order->created_at->diffForHumans() }}</span>
                </div>
            </a>
        @empty
            <p class="empty-hint">No orders yet.</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const orders = @json($chartOrders);
    const payments = @json($paymentSplit);
    const palette = ['#4f46e5', '#10b981', '#f59e0b'];

    new Chart(document.getElementById('vendorOrdersChart'), {
        type: 'bar',
        data: {
            labels: orders.labels,
            datasets: [{ data: orders.data, backgroundColor: '#10b981', borderRadius: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
        },
    });

    const pLabels = payments.map(p => p.label);
    const pData = payments.map(p => p.value);
    if (pLabels.length) {
        new Chart(document.getElementById('vendorPaymentChart'), {
            type: 'doughnut',
            data: { labels: pLabels, datasets: [{ data: pData, backgroundColor: palette, borderWidth: 0 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
            },
        });
    } else {
        document.getElementById('vendorPaymentChart').parentElement.innerHTML = '<p class="empty-hint">No data yet</p>';
    }
})();
</script>
@endpush

@include('partials.web-push-enable', ['endpoint' => route('vendor.fcm-token')])
