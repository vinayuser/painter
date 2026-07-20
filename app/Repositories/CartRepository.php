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
        return $this->model->newQuery()
            ->with(['product.images', 'product.vendor'])
            ->where('user_id', $userId)
            ->get();
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
