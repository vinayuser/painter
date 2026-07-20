<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with('category')
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

        if ($request->hasFile('image')) {
            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $request->file('image')->store('products', 'public'),
                'is_primary' => true,
            ]);
        }

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

        if ($request->hasFile('image')) {
            $product->images()->delete();
            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $request->file('image')->store('products', 'public'),
                'is_primary' => true,
            ]);
        }

        return redirect()->route('vendor.products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorizeProduct($product);
        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Product deleted successfully.');
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
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        if (! $product) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        }

        return $data;
    }
}
