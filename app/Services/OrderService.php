<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VendorPackingStatus;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public const PACKING_MINUTES = 30;

    public const DELIVERY_MINUTES = 30;

    public function __construct(
        protected CartRepository $cartRepository,
        protected OrderRepository $orderRepository,
        protected AddressService $addressService,
        protected NotificationService $notifications,
    ) {}

    public function createFromCart(int $customerId, array $data): Order
    {
        $order = DB::transaction(function () use ($customerId, $data): Order {
            $cartItems = $this->cartRepository->getUserCart($customerId);

            if ($cartItems->isEmpty()) {
                throw new \RuntimeException('Cart is empty.');
            }

            $this->assertSingleVendorCart($cartItems);

            $paymentMethod = PaymentMethod::from($data['payment_method'] ?? PaymentMethod::Online->value);
            $shipping = $this->addressService->resolveShippingDetails($customerId, $data);
            $now = now();
            $packingDeadline = $now->copy()->addMinutes(self::PACKING_MINUTES);
            $deliveryDeadline = $now->copy()->addMinutes(self::DELIVERY_MINUTES);
            $vendorId = $cartItems->first()->product?->vendor_id;
            $total = 0;
            $orderItems = [];

            foreach ($cartItems as $item) {
                $product = $item->product;

                if (! $product || ! $product->is_active) {
                    throw new \RuntimeException("Product {$item->product_id} is unavailable.");
                }

                if ($product->stock_quantity < $item->quantity) {
                    throw new \RuntimeException("Insufficient stock for {$product->name}.");
                }

                $subtotal = $product->price * $item->quantity;
                $total += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $subtotal,
                ];
            }

            $order = $this->orderRepository->create([
                'order_number' => 'ORD-'.strtoupper(Str::random(10)),
                'customer_id' => $customerId,
                'vendor_id' => $vendorId,
                'total_amount' => $total,
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::Pending,
                'order_status' => OrderStatus::Pending,
                'vendor_packing_status' => VendorPackingStatus::Pending,
                'packing_deadline_at' => $packingDeadline,
                'delivery_deadline_at' => $deliveryDeadline,
                'shipping_address' => $shipping['shipping_address'],
                'shipping_phone' => $shipping['shipping_phone'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
                Product::query()
                    ->where('id', $itemData['product_id'])
                    ->decrement('stock_quantity', $itemData['quantity']);
            }

            $this->cartRepository->clearUserCart($customerId);

            return $order->load(['items.product', 'payment', 'vendor', 'customer']);
        });

        $this->notifications->orderPlaced($order);

        return $order;
    }

    protected function assertSingleVendorCart(\Illuminate\Database\Eloquent\Collection $cartItems): void
    {
        $vendorIds = $cartItems
            ->map(fn ($item) => $item->product?->vendor_id)
            ->unique()
            ->values();

        if ($vendorIds->count() > 1) {
            throw new \RuntimeException(
                'Cart contains items from multiple vendors. Remove conflicting items before checkout.'
            );
        }
    }
}
