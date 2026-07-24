@extends('vendor.layout')

@section('title', 'My Products')

@section('content')
<div class="page-header">
    <h1>My Products</h1>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">Add Product</a>
</div>

<div class="card">
    <form method="GET" class="filter-bar">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." style="padding:8px;border:1px solid #cbd5e1;border-radius:6px;flex:1;">
        <button type="submit" class="btn btn-secondary">Search</button>
        @if(request('search'))
            <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">Clear</a>
        @endif
    </form>
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:72px;">Image</th>
                <th>Name</th>
                <th>Category</th>
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
                        <a href="{{ route('vendor.products.edit', $product) }}" class="product-table-thumb">
                            @if($thumb)
                                <img src="{{ asset('storage/'.$thumb->image_path) }}" alt="{{ $product->name }}">
                            @else
                                <span class="product-table-thumb-empty">🛢️</span>
                            @endif
                        </a>
                    </td>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if($product->images->count() > 1)
                            <div class="text-muted" style="font-size:12px;">{{ $product->images->count() }} photos</div>
                        @endif
                    </td>
                    <td>{{ $product->category?->name }}</td>
                    <td>₹{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock_quantity }}</td>
                    <td>
                        @if($product->is_featured)
                            <span class="badge badge-featured">Featured</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $product->is_active ? 'Yes' : 'No' }}</td>
                    <td class="actions">
                        <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-secondary btn-sm">Edit</a>
                        <form action="{{ route('vendor.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;color:var(--text-muted);">No products yet. Add your first product!</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
