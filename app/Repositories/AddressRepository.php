<?php

namespace App\Repositories;

use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Collection;

class AddressRepository extends BaseRepository
{
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

    public function forUser(int $userId): Collection
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();
    }

    public function findForUser(int $userId, int $addressId): ?CustomerAddress
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('id', $addressId)
            ->first();
    }

    public function getDefaultForUser(int $userId): ?CustomerAddress
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('is_default', true)
            ->first();
    }

    public function countForUser(int $userId): int
    {
        return $this->model->newQuery()->where('user_id', $userId)->count();
    }

    public function clearDefaultForUser(int $userId): void
    {
        $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('is_default', true)
            ->update(['is_default' => false]);
    }
}
