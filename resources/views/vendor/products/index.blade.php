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
    </form>
    <div class="table-wrap">
    <table>
        <thead>
            <tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Active</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>₹{{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock_quantity }}</td>
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
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);">No products yet. Add your first product!</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="pagination">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
