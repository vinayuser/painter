@extends('admin.layout')

@section('title', $category->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">{{ $category->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $category->name }}</h1>
    <div>
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <p class="mb-1"><strong>Slug:</strong> <code>{{ $category->slug }}</code></p>
                <p class="mb-1"><strong>Status:</strong> {{ $category->is_active ? 'Active' : 'Inactive' }}</p>
                <p class="mb-0">{{ $category->description ?? 'No description.' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-fill-drip"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Products in category</span>
                <span class="info-box-number">{{ $category->products_count }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Products</h3>
        <div class="card-tools">
            <a href="{{ route('admin.products.create') }}?category_id={{ $category->id }}" class="btn btn-sm btn-primary">Add Product</a>
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Name</th><th>Vendor</th><th>Price</th><th>Stock</th><th></th></tr></thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></td>
                        <td>{{ $product->vendor?->business_name ?? 'Platform' }}</td>
                        <td>₹{{ number_format($product->price, 2) }}</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td><a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No products in this category.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())<div class="card-footer">{{ $products->links() }}</div>@endif
</div>
@endsection
