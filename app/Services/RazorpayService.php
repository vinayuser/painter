<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Razorpay\Api\Api;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    public function createOrder(Order $order): array
    {
        $razorpayOrder = $this->api->order->create([
            'receipt' => $order->order_number,
            'amount' => (int) ($order->total_amount * 100),
            'currency' => 'INR',
            'notes' => [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
            ],
        ]);

        Payment::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'customer_id' => $order->customer_id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $order->total_amount,
                'currency' => 'INR',
                'status' => PaymentStatus::Pending,
            ]
        );

        return [
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => (int) ($order->total_amount * 100),
            'currency' => 'INR',
            'key' => config('services.razorpay.key'),
        ];
    }

    public function verifyPayment(array $data): Payment
    {
        $attributes = [
            'razorpay_order_id' => $data['razorpay_order_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_signature' => $data['razorpay_signature'],
        ];

        $this->api->utility->verifyPaymentSignature($attributes);

        $payment = Payment::query()
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->firstOrFail();

        $payment->update([
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_signature' => $data['razorpay_signature'],
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);

        $payment->order->update([
            'payment_status' => PaymentStatus::Paid,
        ]);

        return $payment->fresh(['order']);
    }

    public function isConfigured(): bool
    {
        return ! empty(config('services.razorpay.key'))
            && ! empty(config('services.razorpay.secret'));
    }
}
