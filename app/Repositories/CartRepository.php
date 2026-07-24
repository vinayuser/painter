<?php

namespace App\Repositories;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

class CartRepository extends BaseRepository
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function getUserCart(int $userId): Collection
    {
        $items = $this->model->newQuery()
            ->with(['product.images', 'product.vendor'])
            ->where('user_id', $userId)
            ->get();

        // Drop cart rows whose product was soft-deleted or removed.
        $staleIds = $items
            ->filter(fn (CartItem $item) => ! $item->product)
            ->pluck('id');

        if ($staleIds->isNotEmpty()) {
            $this->model->newQuery()->whereIn('id', $staleIds)->delete();
            $items = $items->reject(fn (CartItem $item) => ! $item->product)->values();
        }

        return $items;
    }

    public function findUserItem(int $userId, int $productId): ?CartItem
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function clearUserCart(int $userId): void
    {
        $this->model->newQuery()->where('user_id', $userId)->delete();
    }
}
