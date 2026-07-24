@extends('admin.layout')

@section('title', 'Products')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-fill-drip mr-1"></i> Products</h3>
        <div class="card-tools">
            <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Add Product</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="form-control form-control-sm">
            <select name="category_id" class="form-control form-control-sm" style="width:auto;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="featured" class="form-control form-control-sm" style="width:auto;">
                <option value="">Featured: All</option>
                <option value="1" @selected(request('featured') === '1')>Featured only</option>
                <option value="0" @selected(request('featured') === '0')>Not featured</option>
            </select>
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'category_id', 'featured']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-default">Clear</a>
            @endif
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-valign-middle">
                <thead>
                    <tr>
                        <th style="width:72px;">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php $thumb = $product->primaryListingImage(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="product-table-thumb">
                                    @if($thumb)
                                        <img src="{{ asset('storage/'.$thumb->image_path) }}" alt="{{ $product->name }}">
                                    @else
                                        <span class="product-table-thumb-empty"><i class="fas fa-fill-drip"></i></span>
                                    @endif
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.show', $product) }}" class="font-weight-bold">{{ $product->name }}</a>
                                @if($product->images->count() > 1)
                                    <div class="text-muted text-sm"><i class="fas fa-images"></i> {{ $product->images->count() }} photos</div>
                                @endif
                            </td>
                            <td>{{ $product->category?->name }}</td>
                            <td>{{ $product->vendor?->business_name ?? $product->vendor?->name ?? 'Platform' }}</td>
                            <td>₹{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock_quantity }}</td>
                            <td>
                                @if($product->is_featured)
                                    <span class="badge badge-featured">Featured</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
