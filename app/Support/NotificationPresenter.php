<?php

namespace App\Support;

use App\Models\AppNotification;
use App\Models\Order;
use App\Models\PainterBooking;

class NotificationPresenter
{
    public static function typeLabel(string $type): string
    {
        return match ($type) {
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
            'global_broadcast', 'order_placed_broadcast' => 'Announcement',
            default => str_replace('_', ' ', ucfirst($type)),
        };
    }

    public static function icon(string $type): string
    {
        return match ($type) {
            'order_placed' => 'fa-shopping-cart',
            'order_packed' => 'fa-box',
            'delivery_assigned', 'delivery_assigned_customer' => 'fa-user-check',
            'delivery_accepted' => 'fa-handshake',
            'out_for_delivery' => 'fa-shipping-fast',
            'order_delivered', 'order_delivered_vendor' => 'fa-check-circle',
            'booking_created', 'booking_accepted', 'booking_rejected', 'booking_started', 'booking_completed' => 'fa-paint-brush',
            'global_broadcast', 'order_placed_broadcast' => 'fa-bullhorn',
            default => 'fa-bell',
        };
    }

    public static function badgeClass(string $type): string
    {
        return match ($type) {
            'order_placed' => 'badge-blue',
            'order_packed' => 'badge-purple',
            'delivery_assigned', 'delivery_assigned_customer', 'delivery_accepted' => 'badge-yellow',
            'out_for_delivery' => 'badge-blue',
            'order_delivered', 'order_delivered_vendor', 'booking_completed', 'booking_accepted' => 'badge-green',
            'booking_rejected' => 'badge-red',
            default => 'badge-gray',
        };
    }

    public static function link(AppNotification $notification, string $panel = 'admin'): ?string
    {
        $refType = $notification->reference_type;
        $refId = $notification->reference_id;
        $data = $notification->data ?? [];

        if ($refType === Order::class || isset($data['order_id'])) {
            $orderId = $refId ?: ($data['order_id'] ?? null);
            if (! $orderId) {
                return null;
            }

            return match ($panel) {
                'vendor' => route('vendor.orders.show', $orderId),
                'delivery' => route('delivery.orders.show', $orderId),
                default => route('admin.orders.show', $orderId),
            };
        }

        if ($refType === PainterBooking::class || isset($data['booking_id'])) {
            $bookingId = $refId ?: ($data['booking_id'] ?? null);
            if (! $bookingId) {
                return null;
            }

            return $panel === 'admin' ? route('admin.bookings.show', $bookingId) : null;
        }

        return null;
    }
}
