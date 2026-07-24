@extends('vendor.layout')

@section('title', $product->exists ? 'Edit Product' : 'Add Product')

@section('content')
<form method="POST"
      action="{{ $product->exists ? route('vendor.products.update', $product) : route('vendor.products.store') }}"
      enctype="multipart/form-data"
      class="product-form">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="page-header">
        <div>
            <h1>{{ $product->exists ? 'Edit Product' : 'Add Product' }}</h1>
            <p class="dash-subtitle" style="margin:4px 0 0;">Choose an admin category, add photos, and publish to the marketplace.</p>
        </div>
        <div class="dash-actions">
            <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Product</button>
        </div>
    </div>

    <div class="product-form-grid">
        <div class="product-form-main">
            <div class="card">
                <h3 class="card-section-title">Basic details</h3>
                <div class="form-group">
                    <label for="name">Product name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required placeholder="e.g. WeatherShield Exterior 4L">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price">Price (₹) *</label>
                        <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="stock_quantity">Stock quantity *</label>
                        <input type="number" min="0" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" required>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;gap:1.25rem;padding-bottom:4px;">
                        <label class="checkbox-label" style="margin:0;">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                            Active
                        </label>
                        <label class="checkbox-label" style="margin:0;">
                            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))>
                            Featured
                        </label>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5" placeholder="Coverage, finish, recommended use…">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <div class="card">
                <h3 class="card-section-title">Product gallery</h3>
                <p style="color:var(--text-muted);margin-bottom:12px;font-size:13px;">Upload multiple photos (max 8). Mark one as primary for listings.</p>

                @if($product->exists && $product->images->isNotEmpty())
                    <div class="product-gallery-grid" style="margin-bottom:14px;">
                        @foreach($product->images as $image)
                            <div class="product-gallery-item {{ $image->is_primary ? 'is-primary' : '' }}">
                                <img src="{{ asset('storage/'.$image->image_path) }}" alt="">
                                <div class="product-gallery-meta">
                                    <label>
                                        <input type="radio" name="primary_image_id" value="{{ $image->id }}" @checked($image->is_primary)>
                                        Primary
                                    </label>
                                    <label style="color:var(--danger);">
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
                        <strong>Click to upload</strong> or drop images here
                        <div style="color:var(--text-muted);font-size:12px;margin-top:6px;">JPG / PNG · max 5MB each</div>
                    </div>
                </div>
                <div id="image-preview" class="product-preview-grid" style="margin-top:12px;"></div>
            </div>
        </div>

        <div class="product-form-side">
            <div class="card">
                <h3 class="card-section-title">Publish</h3>
                <p style="color:var(--text-muted);font-size:13px;margin-bottom:14px;">Featured products show in the public featured API for the mobile home screen.</p>
                <button type="submit" class="btn btn-primary" style="width:100%;">Save Product</button>
                <a href="{{ route('vendor.products.index') }}" class="btn btn-secondary" style="width:100%;margin-top:8px;text-align:center;">Cancel</a>
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
