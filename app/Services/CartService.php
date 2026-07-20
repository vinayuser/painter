<?php

namespace App\Services;

use App\Exceptions\DifferentVendorCartException;
use App\Models\Product;
use App\Models\User;
use App\Repositories\AddressRepository;
use App\Repositories\CartRepository;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    public function __construct(
        protected CartRepository $cartRepository,
        protected AddressRepository $addressRepository,
    ) {}

    public function addItem(int $userId, int $productId, int $quantity, bool $replace = false): CartItem
    {
        $product = Product::query()->with('vendor')->findOrFail($productId);

        if (! $product->is_active) {
            throw new \RuntimeException('Product is unavailable.');
        }

        $existing = $this->cartRepository->findUserItem($userId, $productId);

        if ($existing) {
            $existing->update(['quantity' => $existing->quantity + $quantity]);

            return $existing->fresh(['product.images', 'product.vendor']);
        }

        $cartItems = $this->cartRepository->getUserCart($userId);

        if ($cartItems->isNotEmpty()) {
            $cartVendorId = $this->vendorIdFromCart($cartItems);
            $productVendorId = $product->vendor_id;

            if ($cartVendorId !== $productVendorId) {
                if (! $replace) {
                    throw new DifferentVendorCartException(
                        $this->vendorUserFromCart($cartItems),
                        $product->vendor,
                    );
                }

                $this->cartRepository->clearUserCart($userId);
            }
        }

        return $this->cartRepository->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ])->load(['product.images', 'product.vendor']);
    }

    public function increment(int $userId, int $productId): CartItem
    {
        $item = $this->cartRepository->findUserItem($userId, $productId);

        if (! $item) {
            throw new \RuntimeException('Cart item not found.');
        }

        $item->update(['quantity' => $item->quantity + 1]);

        return $item->fresh(['product.images', 'product.vendor']);
    }

    public function decrement(int $userId, int $productId): ?CartItem
    {
        $item = $this->cartRepository->findUserItem($userId, $productId);

        if (! $item) {
            throw new \RuntimeException('Cart item not found.');
        }

        if ($item->quantity <= 1) {
            $item->delete();

            return null;
        }

        $item->update(['quantity' => $item->quantity - 1]);

        return $item->fresh(['product.images', 'product.vendor']);
    }

    public function updateQuantity(int $userId, int $productId, int $quantity): CartItem
    {
        $item = $this->cartRepository->findUserItem($userId, $productId);

        if (! $item) {
            throw new \RuntimeException('Cart item not found.');
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh(['product.images', 'product.vendor']);
    }

    public function removeItem(int $userId, int $productId): void
    {
        $item = $this->cartRepository->findUserItem($userId, $productId);

        if ($item) {
            $item->delete();
        }
    }

    public function getCheckoutSummary(int $userId): array
    {
        $items = $this->cartRepository->getUserCart($userId);
        $subtotal = 0;
        $itemCount = 0;

        foreach ($items as $item) {
            if ($item->product) {
                $subtotal += $item->product->price * $item->quantity;
                $itemCount += $item->quantity;
            }
        }

        $addresses = $this->addressRepository->forUser($userId);
        $defaultAddress = $addresses->firstWhere('is_default', true);
        $vendor = $items->isNotEmpty() ? $this->vendorUserFromCart($items) : null;

        return [
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'vendor' => $vendor,
            'vendor_label' => $vendor
                ? ($vendor->business_name ?: $vendor->name)
                : ($items->isNotEmpty() ? 'Paint Store' : null),
            'addresses' => $addresses,
            'default_address' => $defaultAddress,
        ];
    }

    protected function vendorIdFromCart(Collection $cartItems): ?int
    {
        $firstProduct = $cartItems->first()?->product;

        return $firstProduct?->vendor_id;
    }

    protected function vendorUserFromCart(Collection $cartItems): ?User
    {
        $firstProduct = $cartItems->first()?->product;

        if (! $firstProduct) {
            return null;
        }

        if ($firstProduct->relationLoaded('vendor')) {
            return $firstProduct->vendor;
        }

        return $firstProduct->vendor()->first();
    }
}
