@extends('admin.layout')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
<div class="page-header">
    <h1>{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @if($product->exists) @method('PUT') @endif

        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price (₹)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
            </div>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description">{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*">
            @if($product->exists && $product->images->first())
                <p style="margin-top:8px;"><img src="{{ asset('storage/'.$product->images->first()->image_path) }}" class="product-thumb" alt=""></p>
            @endif
        </div>
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                Active
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
@endsection
