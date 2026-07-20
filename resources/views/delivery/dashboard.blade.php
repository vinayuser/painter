@extends('delivery.layout')

@section('title', 'Dashboard')

@section('content')
<div class="dash-header">
    <div>
        <h1 class="dash-title">Delivery Dashboard</h1>
        <p class="dash-subtitle">{{ auth()->user()->name }} · {{ now()->format('M j, Y') }}</p>
    </div>
    <a href="{{ route('delivery.orders.index') }}" class="btn btn-primary btn-sm">Active deliveries</a>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
    <div class="kpi-card kpi-warning">
        <div class="kpi-icon">🚚</div>
        <div class="kpi-body">
            <span class="kpi-value">{{ $activeDeliveries }}</span>
            <span class="kpi-label">Active deliveries</span>
        </div>
    </div>
    <div class="kpi-card kpi-success">
        <div class="kpi-icon">✅</div>
        <div class="kpi-body">
            <span class="kpi-value">{{ $completedDeliveries }}</span>
            <span class="kpi-label">Completed</span>
        </div>
    </div>
    <div class="kpi-card kpi-primary">
        <div class="kpi-icon">💵</div>
        <div class="kpi-body">
            <span class="kpi-value">₹{{ number_format($codCollected, 0) }}</span>
            <span class="kpi-label">COD collected</span>
        </div>
    </div>
</div>

<div class="card chart-card">
    <div class="card-head"><h3>Deliveries completed — Last 7 days</h3></div>
    <div class="chart-wrap"><canvas id="deliveryChart"></canvas></div>
</div>

<div class="card">
    <div class="card-head">
        <h3>Recent assignments</h3>
        <a href="{{ route('delivery.orders.index', ['filter' => 'past']) }}" class="link-sm">Past deliveries</a>
    </div>
    <div class="order-feed">
        @forelse($recentDeliveries as $order)
            <a href="{{ route('delivery.orders.show', $order) }}" class="order-feed-item">
                <div class="order-feed-top">
                    <strong>{{ $order->order_number }}</strong>
                    <span class="badge badge-blue">{{ $order->order_status->label() }}</span>
                </div>
                <div class="order-feed-meta">{{ $order->customer->name }} · {{ $order->shipping_address }}</div>
                <div class="order-feed-bottom">
                    <span class="order-amount">₹{{ number_format($order->total_amount, 0) }}</span>
                    <span class="badge badge-gray">{{ $order->payment_method->label() }}</span>
                </div>
            </a>
        @empty
            <p class="empty-hint">No deliveries assigned yet.</p>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const data = @json($chartDeliveries);
    new Chart(document.getElementById('deliveryChart'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                borderColor: '#1d4ed8',
                backgroundColor: 'rgba(29,78,216,.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } },
        },
    });
})();
</script>
@endpush
