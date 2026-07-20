<?php

namespace App\Services;

use App\Enums\FcmTopic;
use App\Enums\UserRole;
use App\Jobs\SendPushNotificationJob;
use App\Models\AppNotification;
use App\Models\Order;
use App\Models\PainterBooking;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function __construct(
        protected FcmService $fcm,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        ?User $user,
        string $title,
        string $body,
        string $type,
        array $data = [],
        ?string $referenceType = null,
        ?int $referenceId = null,
        bool $queue = false,
    ): void {
        if (! $user) {
            return;
        }

        AppNotification::query()->create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'channel' => $user->role?->value,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'data' => $data,
        ]);

        if (blank($user->fcm_token)) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'token' => $user->fcm_token,
            'data' => array_merge($data, [
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]),
        ];

        if ($queue) {
            SendPushNotificationJob::dispatch($payload);
        } else {
            $this->fcm->sendToToken($payload['token'], $title, $body, $payload['data']);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyTopic(
        FcmTopic|UserRole $topicOrRole,
        string $title,
        string $body,
        string $type,
        array $data = [],
        bool $queue = false,
    ): void {
        $topic = $topicOrRole instanceof UserRole
            ? FcmTopic::forRole($topicOrRole)
            : $topicOrRole;

        AppNotification::query()->create([
            'user_id' => null,
            'title' => $title,
            'body' => $body,
            'type' => $type,
            'channel' => $topic->value,
            'data' => $data,
        ]);

        $payload = [
            'title' => $title,
            'body' => $body,
            'topic' => $topic->value,
            'data' => array_merge($data, [
                'type' => $type,
                'channel' => $topic->value,
            ]),
        ];

        if ($queue) {
            SendPushNotificationJob::dispatch($payload);
        } else {
            $this->fcm->sendToTopic($topic, $title, $body, $payload['data']);
        }
    }

    public function orderPlaced(Order $order): void
    {
        $order->loadMissing(['vendor', 'customer']);

        $title = 'New Order Received';
        $body = "Order {$order->order_number} — ₹".number_format((float) $order->total_amount, 2);
        $data = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'click_action' => 'ORDER_DETAIL',
        ];

        $this->notifyUser($order->vendor, $title, $body, 'order_placed', $data, Order::class, $order->id);

        $this->notifyAdmins(
            $title,
            "{$order->customer?->name} placed {$order->order_number}",
            'order_placed',
            $data,
            Order::class,
            $order->id,
        );
    }

    public function orderPacked(Order $order): void
    {
        $order->loadMissing(['customer', 'vendor']);

        $this->notifyUser(
            $order->customer,
            'Order Packed',
            "Your order {$order->order_number} has been packed and is ready for delivery.",
            'order_packed',
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'click_action' => 'ORDER_DETAIL'],
            Order::class,
            $order->id,
        );

        $this->notifyAdmins(
            'Order Packed',
            "{$order->order_number} packed by ".($order->vendor?->business_name ?: $order->vendor?->name),
            'order_packed',
            ['order_id' => $order->id, 'order_number' => $order->order_number],
            Order::class,
            $order->id,
        );
    }

    public function deliveryAssigned(Order $order): void
    {
        $order->loadMissing(['customer', 'deliveryAgent', 'vendor']);

        $this->notifyUser(
            $order->deliveryAgent,
            'New Delivery Assigned',
            "Order {$order->order_number} has been assigned to you.",
            'delivery_assigned',
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'click_action' => 'DELIVERY_DETAIL'],
            Order::class,
            $order->id,
        );

        $this->notifyUser(
            $order->customer,
            'Delivery Partner Assigned',
            "A delivery partner has been assigned for order {$order->order_number}.",
            'delivery_assigned_customer',
            ['order_id' => $order->id, 'order_number' => $order->order_number, 'click_action' => 'ORDER_DETAIL'],
            Order::class,
            $order->id,
        );
    }

    public function deliveryAccepted(Order $order): void
    {
        $order->loadMissing('customer');

        $this->notifyUser(
            $order->customer,
            'Delivery Accepted',
            "Your delivery partner accepted order {$order->order_number}.",
            'delivery_accepted',
            ['order_id' => $order->id, 'order_number' => $order->order_number],
            Order::class,
            $order->id,
        );
    }

    public function outForDelivery(Order $order): void
    {
        $order->loadMissing('customer');

        $this->notifyUser(
            $order->customer,
            'Out for Delivery',
            "Order {$order->order_number} is on the way.",
            'out_for_delivery',
            ['order_id' => $order->id, 'order_number' => $order->order_number],
            Order::class,
            $order->id,
        );
    }

    public function orderDelivered(Order $order): void
    {
        $order->loadMissing(['customer', 'vendor']);
        $data = ['order_id' => $order->id, 'order_number' => $order->order_number];

        $this->notifyUser(
            $order->customer,
            'Order Delivered',
            "Order {$order->order_number} has been delivered. Thank you!",
            'order_delivered',
            $data,
            Order::class,
            $order->id,
        );

        $this->notifyUser(
            $order->vendor,
            'Order Delivered',
            "Order {$order->order_number} was delivered successfully.",
            'order_delivered_vendor',
            $data,
            Order::class,
            $order->id,
        );

        $this->notifyAdmins('Order Delivered', "{$order->order_number} delivered", 'order_delivered', $data, Order::class, $order->id);
    }

    public function bookingCreated(PainterBooking $booking): void
    {
        $booking->loadMissing(['painter', 'customer']);

        $this->notifyUser(
            $booking->painter,
            'New Painter Booking',
            "{$booking->customer?->name} booked you for {$booking->booking_date}.",
            'booking_created',
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number, 'click_action' => 'BOOKING_DETAIL'],
            PainterBooking::class,
            $booking->id,
        );
    }

    public function bookingAccepted(PainterBooking $booking): void
    {
        $booking->loadMissing('customer');

        $this->notifyUser(
            $booking->customer,
            'Booking Accepted',
            "Your painter accepted booking {$booking->booking_number}.",
            'booking_accepted',
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number],
            PainterBooking::class,
            $booking->id,
        );
    }

    public function bookingRejected(PainterBooking $booking): void
    {
        $booking->loadMissing('customer');

        $this->notifyUser(
            $booking->customer,
            'Booking Rejected',
            "Your painter declined booking {$booking->booking_number}.",
            'booking_rejected',
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number],
            PainterBooking::class,
            $booking->id,
        );
    }

    public function bookingStarted(PainterBooking $booking): void
    {
        $booking->loadMissing('customer');

        $this->notifyUser(
            $booking->customer,
            'Work Started',
            "Work has started for booking {$booking->booking_number}.",
            'booking_started',
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number],
            PainterBooking::class,
            $booking->id,
        );
    }

    public function bookingCompleted(PainterBooking $booking): void
    {
        $booking->loadMissing('customer');

        $this->notifyUser(
            $booking->customer,
            'Work Completed',
            "Booking {$booking->booking_number} is marked completed.",
            'booking_completed',
            ['booking_id' => $booking->id, 'booking_number' => $booking->booking_number],
            PainterBooking::class,
            $booking->id,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyAdmins(
        string $title,
        string $body,
        string $type,
        array $data = [],
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): void {
        /** @var Collection<int, User> $admins */
        $admins = User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->get();

        // Per-admin inbox + device token only (topic would duplicate Chrome pushes for the same browser).
        foreach ($admins as $admin) {
            $this->notifyUser($admin, $title, $body, $type, $data, $referenceType, $referenceId);
        }
    }
}
