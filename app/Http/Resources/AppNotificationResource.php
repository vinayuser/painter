<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\PainterBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'type_label' => $this->typeLabel(),
            'channel' => $this->channel,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'data' => $this->data ?? [],
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }

    protected function typeLabel(): string
    {
        return match ($this->type) {
            'order_placed' => 'New Order',
            'order_packed' => 'Order Packed',
            'delivery_assigned', 'delivery_assigned_customer' => 'Delivery Assigned',
            'delivery_accepted' => 'Delivery Accepted',
            'out_for_delivery' => 'Out for Delivery',
            'order_delivered', 'order_delivered_vendor' => 'Order Delivered',
            'booking_created' => 'New Booking',
            'booking_accepted' => 'Booking Accepted',
            'booking_rejected' => 'Booking Rejected',
            'booking_started' => 'Work Started',
            'booking_completed' => 'Work Completed',
            'global_broadcast' => 'Announcement',
            default => str_replace('_', ' ', ucfirst((string) $this->type)),
        };
    }
}
