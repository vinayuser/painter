@extends('admin.layout')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->exists ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<form method="POST"
      action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
      enctype="multipart/form-data"
      class="product-form">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="page-header">
        <div>
            <h1 class="mb-0">{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>
            <p class="text-muted mb-0 mt-1">{{ $product->exists ? 'Update catalog details, gallery, and visibility.' : 'Create a catalog item with photos and featured placement.' }}</p>
        </div>
        <div class="d-flex" style="gap:.5rem;">
            <a href="{{ route('admin.products.index') }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Product</button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Basic details</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Product name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control form-control-lg"
                               value="{{ old('name', $product->name) }}" required placeholder="e.g. Royal Emulsion White 10L">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug <small class="text-muted">(optional)</small></label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                   value="{{ old('slug', $product->slug) }}" placeholder="auto-from-name">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="5"
                                  placeholder="Finish, coverage, suitable surfaces…">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-images mr-1"></i> Product gallery</h3></div>
                <div class="card-body">
                    <p class="text-muted text-sm">Upload up to 8 images. First new upload becomes primary if none is set.</p>

                    @if($product->exists && $product->images->isNotEmpty())
                        <div class="product-gallery-grid mb-3">
                            @foreach($product->images as $image)
                                <div class="product-gallery-item {{ $image->is_primary ? 'is-primary' : '' }}">
                                    <img src="{{ asset('storage/'.$image->image_path) }}" alt="">
                                    <div class="product-gallery-meta">
                                        <label class="mb-0">
                                            <input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked($image->is_primary)>
                                            Primary
                                        </label>
                                        <label class="mb-0 text-danger">
                                            <input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}">
                                            Remove
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="product-dropzone" id="product-dropzone">
                        <input type="file" name="images[]" id="images" accept="image/*" multiple>
                        <div class="product-dropzone-inner">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                            <strong>Click to upload</strong> or drag images here
                            <div class="text-muted text-sm mt-1">JPG, PNG, WEBP · max 5MB each · up to 8 files</div>
                        </div>
                    </div>
                    <div id="image-preview" class="product-preview-grid mt-3"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-tags mr-1"></i> Pricing & stock</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="price">Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" id="price" name="price" class="form-control form-control-lg"
                               value="{{ old('price', $product->price) }}" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="stock_quantity">Stock quantity <span class="text-danger">*</span></label>
                        <input type="number" min="0" id="stock_quantity" name="stock_quantity" class="form-control"
                               value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-eye mr-1"></i> Visibility</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
                               @checked(old('is_active', $product->is_active ?? true))>
                        <label class="custom-control-label" for="is_active">Active in catalog</label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_featured" name="is_featured" value="1"
                               @checked(old('is_featured', $product->is_featured ?? false))>
                        <label class="custom-control-label" for="is_featured">Featured product</label>
                    </div>
                    <p class="text-muted text-sm mt-3 mb-0">Featured items appear on <code>GET /api/v1/products/featured</code>.</p>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save mr-1"></i> Save Product</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('images');
    const preview = document.getElementById('image-preview');
    const zone = document.getElementById('product-dropzone');
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        preview.innerHTML = '';
        Array.from(input.files || []).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const el = document.createElement('div');
                el.className = 'product-preview-item';
                el.innerHTML = '<img src="' + e.target.result + '" alt="">';
                preview.appendChild(el);
            };
            reader.readAsDataURL(file);
        });
    });

    ['dragenter', 'dragover'].forEach(function (evt) {
        zone.addEventListener(evt, function (e) {
            e.preventDefault();
            zone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (evt) {
        zone.addEventListener(evt, function (e) {
            e.preventDefault();
            zone.classList.remove('is-dragover');
        });
    });
    zone.addEventListener('drop', function (e) {
        if (!e.dataTransfer.files.length) return;
        input.files = e.dataTransfer.files;
        input.dispatchEvent(new Event('change'));
    });
})();
</script>
@endpush
