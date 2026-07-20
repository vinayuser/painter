@extends('admin.layout')

@section('title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
<div class="page-header">
    <h1>{{ $product->name }}</h1>
    <div>
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-default">Back</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        @if($product->images->isNotEmpty())
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ asset('storage/'.$product->images->first()->image_path) }}" class="img-fluid rounded" alt="{{ $product->name }}" style="max-height:280px;">
                    @if($product->images->count() > 1)
                        <div class="row mt-2">
                            @foreach($product->images->skip(1) as $img)
                                <div class="col-4"><img src="{{ asset('storage/'.$img->image_path) }}" class="img-fluid rounded product-thumb" alt=""></div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-body">
                <h4 class="text-success mb-0">₹{{ number_format($product->price, 2) }}</h4>
                <small class="text-muted">Stock: {{ $product->stock_quantity }} units</small>
                <hr>
                <p class="mb-1"><strong>Category:</strong> <a href="{{ route('admin.categories.show', $product->category) }}">{{ $product->category->name }}</a></p>
                <p class="mb-1"><strong>Vendor:</strong> {{ $product->vendor?->business_name ?? $product->vendor?->name ?? 'Platform' }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ $product->is_active ? 'Active' : 'Inactive' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Description</h3></div>
            <div class="card-body">
                {!! nl2br(e($product->description ?? 'No description.')) !!}
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h3 class="card-title">Product Info</h3></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-3">Slug</dt><dd class="col-9"><code>{{ $product->slug }}</code></dd>
                    <dt class="col-3">SKU / ID</dt><dd class="col-9">#{{ $product->id }}</dd>
                    <dt class="col-3">Created</dt><dd class="col-9">{{ $product->created_at->format('d M Y') }}</dd>
                    <dt class="col-3">Updated</dt><dd class="col-9">{{ $product->updated_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
