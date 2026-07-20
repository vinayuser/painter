<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function forCustomer(int $customerId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['items.product', 'payment', 'deliveryAgent', 'vendor'])
            ->where('customer_id', $customerId)
            ->latest()
            ->paginate($perPage);
    }

    public function forDeliveryAgent(int $agentId, int $perPage = 15, ?string $filter = null): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['items', 'customer', 'payment', 'vendor'])
            ->where('delivery_agent_id', $agentId)
            ->when($filter === 'pending', fn ($q) => $q->where('order_status', '!=', \App\Enums\OrderStatus::Delivered))
            ->when($filter === 'completed', fn ($q) => $q->where('order_status', \App\Enums\OrderStatus::Delivered))
            ->latest()
            ->paginate($perPage);
    }

    public function forVendor(int $vendorId, int $perPage = 15, ?string $filter = null): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['items', 'customer', 'deliveryAgent', 'payment'])
            ->where('vendor_id', $vendorId)
            ->when($filter === 'active', fn ($q) => $q->whereNotIn('order_status', [
                \App\Enums\OrderStatus::Delivered,
                \App\Enums\OrderStatus::Cancelled,
            ]))
            ->when($filter === 'past', fn ($q) => $q->where('order_status', \App\Enums\OrderStatus::Delivered))
            ->latest()
            ->paginate($perPage);
    }

    public function findWithRelations(int $id): ?Order
    {
        return $this->model->newQuery()
            ->with(['items.product', 'payment', 'customer', 'deliveryAgent', 'vendor'])
            ->find($id);
    }
}
