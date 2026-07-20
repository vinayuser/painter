<?php

namespace App\Repositories;

use App\Enums\BookingStatus;
use App\Models\PainterBooking;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PainterBookingRepository extends BaseRepository
{
    public function __construct(PainterBooking $model)
    {
        parent::__construct($model);
    }

    public function forCustomer(int $customerId, int $perPage = 15, ?string $filter = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['painter', 'images'])
            ->where('customer_id', $customerId)
            ->when($filter, fn (Builder $q) => $this->applyFilter($q, $filter))
            ->latest()
            ->paginate($perPage);
    }

    public function forPainter(int $painterId, int $perPage = 15, ?string $filter = null): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['customer', 'images'])
            ->where('painter_id', $painterId)
            ->when($filter, fn (Builder $q) => $this->applyFilter($q, $filter))
            ->latest()
            ->paginate($perPage);
    }

    public function findWithRelations(int $id): ?PainterBooking
    {
        return $this->baseQuery()
            ->with(['customer', 'painter', 'images'])
            ->find($id);
    }

    private function baseQuery(): Builder
    {
        return $this->model->newQuery();
    }

    private function applyFilter(Builder $query, string $filter): void
    {
        match ($filter) {
            'new' => $query->where('status', BookingStatus::Assigned),
            'upcoming' => $query->whereIn('status', [
                BookingStatus::Assigned,
                BookingStatus::Accepted,
                BookingStatus::InProgress,
            ])->where('booking_date', '>=', now()->toDateString()),
            'completed' => $query->where('status', BookingStatus::Completed),
            default => null,
        };
    }
}
