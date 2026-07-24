<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Concerns\ManagesProductImages;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    use ManagesProductImages;

    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->where('vendor_id', auth()->id())
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15);

        return view('vendor.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('vendor.products.form', [
            'product' => new Product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);
        $data['vendor_id'] = auth()->id();
        $product = Product::query()->create($data);
        $this->syncProductImages($request, $product);

        return redirect()->route('vendor.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeProduct($product);
        $product->load('images');

        return view('vendor.products.form', [
            'product' => $product,
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $product->update($this->validateProduct($request, $product));
        $this->syncProductImages($request, $product->fresh('images'));

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $product->delete();

        return redirect()->route('vendor.products.index')->with(
            'success',
            'Product removed from catalog. Existing orders are unchanged; cart items for this product were cleared.'
        );
    }

    private function authorizeProduct(Product $product): void
    {
        if ($product->vendor_id !== auth()->id()) {
            abort(403);
        }
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'max:5120'],
            'image' => ['nullable', 'image', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],
            'primary_image_id' => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        if (! $product) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        }

        unset($data['images'], $data['image'], $data['remove_image_ids'], $data['primary_image_id']);

        return $data;
    }
}
