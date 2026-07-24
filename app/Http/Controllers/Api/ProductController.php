<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productRepository->searchAndFilter(
            $request->only(['search', 'category_id', 'min_price', 'max_price', 'sort_by', 'sort_dir', 'featured']),
            (int) $request->get('per_page', 15)
        );

        return ProductResource::collection($products)->response();
    }

    public function featured(Request $request): JsonResponse
    {
        $products = $this->productRepository->featured(
            (int) $request->get('per_page', 15)
        );

        return ProductResource::collection($products)->response();
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);
        $product->load(['category', 'images', 'vendor']);

        if (! $product->is_active) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json(['data' => new ProductResource($product)]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json(['data' => CategoryResource::collection($categories)]);
    }

    public function byCategory(int $categoryId, Request $request): JsonResponse
    {
        $category = Category::query()->where('is_active', true)->find($categoryId);

        if (! $category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        $products = $this->productRepository->searchAndFilter(
            array_merge($request->only(['search', 'min_price', 'max_price', 'sort_by', 'sort_dir']), ['category_id' => $categoryId]),
            (int) $request->get('per_page', 15)
        );

        return response()->json([
            'category' => new CategoryResource($category),
            'products' => ProductResource::collection($products),
        ]);
    }
}
