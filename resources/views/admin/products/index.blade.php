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
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td><a href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a></td>
                            <td>{{ $product->category->name }}</td>
                            <td>{{ $product->vendor?->business_name ?? $product->vendor?->name ?? 'Platform' }}</td>
                            <td>₹{{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock_quantity }}</td>
                            <td>{{ $product->is_active ? 'Yes' : 'No' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">{{ $products->withQueryString()->links() }}</div>
</div>
@endsection
