@extends('admin.layout')

@section('title', 'Reports')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Sales Reports</h3>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar mb-4">
            <label class="mb-0 align-self-center text-sm font-weight-bold">From</label>
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;">
            <label class="mb-0 align-self-center text-sm font-weight-bold">To</label>
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-control form-control-sm" style="width:auto;">
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-sync"></i> Generate</button>
        </form>

        <div class="row mb-4">
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>{{ $stats['total_orders'] }}</strong><span>Total Orders</span></div></div>
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>{{ $stats['delivered'] }}</strong><span>Delivered</span></div></div>
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>{{ $stats['cancelled'] }}</strong><span>Cancelled</span></div></div>
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>₹{{ number_format($stats['revenue'], 0) }}</strong><span>Total Revenue</span></div></div>
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>₹{{ number_format($stats['online_revenue'], 0) }}</strong><span>Online</span></div></div>
            <div class="col-md-2 col-6"><div class="stat-tile"><strong>₹{{ number_format($stats['cod_revenue'], 0) }}</strong><span>COD</span></div></div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Daily Orders & Revenue</h3></div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th></tr></thead>
                            <tbody>
                                @forelse($dailyRevenue as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->day)->format('d M Y') }}</td>
                                        <td>{{ $row->orders }}</td>
                                        <td>₹{{ number_format($row->revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No data for selected period.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Top Products</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
                            <tbody>
                                @forelse($topProducts as $row)
                                    <tr>
                                        <td>{{ $row->product_name }}</td>
                                        <td>{{ $row->qty }}</td>
                                        <td>₹{{ number_format($row->revenue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No sales data.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="info-box mt-3">
                    <span class="info-box-icon bg-info"><i class="fas fa-receipt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Average Order Value</span>
                        <span class="info-box-number">₹{{ number_format($stats['avg_order_value'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
