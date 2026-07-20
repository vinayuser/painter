<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DifferentVendorCartException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\CheckoutResource;
use App\Http\Resources\VendorSummaryResource;
use App\Repositories\CartRepository;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected CartService $cartService,
    ) {}

    public function index(): JsonResponse
    {
        $summary = $this->cartService->getCheckoutSummary(auth('api')->id());

        return response()->json([
            'data' => CartItemResource::collection($summary['items']),
            'summary' => [
                'item_count' => $summary['item_count'],
                'subtotal' => (float) $summary['subtotal'],
                'total_amount' => (float) $summary['total_amount'],
                'vendor' => $summary['vendor']
                    ? new VendorSummaryResource($summary['vendor'])
                    : null,
                'vendor_label' => $summary['vendor_label'],
            ],
        ]);
    }

    public function checkout(): JsonResponse
    {
        $summary = $this->cartService->getCheckoutSummary(auth('api')->id());

        if ($summary['items']->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty.'], 422);
        }

        return response()->json([
            'message' => 'Checkout summary.',
            'data' => new CheckoutResource($summary),
        ]);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        try {
            $item = $this->cartService->addItem(
                auth('api')->id(),
                $request->integer('product_id'),
                $request->integer('quantity'),
                $request->boolean('replace'),
            );

            return response()->json([
                'message' => 'Product added to cart.',
                'data' => new CartItemResource($item),
            ], 201);
        } catch (DifferentVendorCartException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'different_vendor',
                'cart_vendor' => $e->cartVendor
                    ? new VendorSummaryResource($e->cartVendor)
                    : null,
                'product_vendor' => $e->productVendor
                    ? new VendorSummaryResource($e->productVendor)
                    : null,
            ], 409);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function increment(int $productId): JsonResponse
    {
        try {
            $item = $this->cartService->increment(auth('api')->id(), $productId);

            return response()->json([
                'message' => 'Quantity increased.',
                'data' => new CartItemResource($item),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function decrement(int $productId): JsonResponse
    {
        try {
            $item = $this->cartService->decrement(auth('api')->id(), $productId);

            return response()->json([
                'message' => $item ? 'Quantity decreased.' : 'Item removed from cart.',
                'data' => $item ? new CartItemResource($item) : null,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(UpdateCartRequest $request, int $productId): JsonResponse
    {
        try {
            $item = $this->cartService->updateQuantity(
                auth('api')->id(),
                $productId,
                $request->integer('quantity')
            );

            return response()->json([
                'message' => 'Cart updated.',
                'data' => new CartItemResource($item),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $productId): JsonResponse
    {
        $this->cartService->removeItem(auth('api')->id(), $productId);

        return response()->json(['message' => 'Item removed from cart.']);
    }
}
