<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DeliveryService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {}

    public function acceptDelivery(Order $order): Order
    {
        $order->update([
            'delivery_status' => DeliveryStatus::Accepted,
            'order_status' => OrderStatus::Assigned,
        ]);

        $order = $order->fresh(['items', 'customer', 'payment']);
        $this->notifications->deliveryAccepted($order);

        return $order;
    }

    public function updateStatus(Order $order, DeliveryStatus $status): Order
    {
        $data = ['delivery_status' => $status];

        if ($status === DeliveryStatus::OutForDelivery) {
            $data['order_status'] = OrderStatus::OutForDelivery;
        }

        if ($status === DeliveryStatus::Delivered) {
            $data['order_status'] = OrderStatus::Delivered;
            $data['delivered_at'] = now();

            if ($order->payment_method === PaymentMethod::Cod) {
                $data['payment_status'] = PaymentStatus::Paid;
                $this->recordCodPayment($order);
            }
        }

        $order->update($data);
        $order = $order->fresh(['items', 'customer', 'payment', 'vendor']);

        if ($status === DeliveryStatus::OutForDelivery) {
            $this->notifications->outForDelivery($order);
        }

        if ($status === DeliveryStatus::Delivered) {
            $this->notifications->orderDelivered($order);
        }

        return $order;
    }

    protected function recordCodPayment(Order $order): void
    {
        Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => $order->customer_id,
                'amount' => $order->grandTotal(),
                'currency' => 'INR',
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'metadata' => [
                    'method' => PaymentMethod::Cod->value,
                    'product_amount' => (float) $order->total_amount,
                    'delivery_charge' => (float) $order->delivery_charge,
                    'collected_by_delivery_agent_id' => $order->delivery_agent_id,
                ],
            ]
        );
    }

    public function uploadProof(Order $order, UploadedFile $file): Order
    {
        if ($order->delivery_proof_path) {
            Storage::disk('public')->delete($order->delivery_proof_path);
        }

        $path = $file->store("deliveries/{$order->id}", 'public');
        $order->update(['delivery_proof_path' => $path]);

        return $order->fresh(['items', 'customer', 'payment']);
    }
}
