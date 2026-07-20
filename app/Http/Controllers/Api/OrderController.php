<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use App\Repositories\OrderRepository;
use App\Services\OrderService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderService $orderService,
        protected RazorpayService $razorpayService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->forCustomer(
            auth('api')->id(),
            (int) $request->get('per_page', 15)
        );

        return OrderResource::collection($orders)->response();
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createFromCart(
                auth('api')->id(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Order created successfully.',
                'data' => new OrderResource($order),
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $this->authorize('view', $order);

        return response()->json(['data' => new OrderResource($order)]);
    }

    public function initiatePayment(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order || $order->customer_id !== auth('api')->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->payment_method?->value === 'cod') {
            return response()->json(['message' => 'This order uses Cash on Delivery. No online payment required.'], 422);
        }

        if (! $this->razorpayService->isConfigured()) {
            return response()->json(['message' => 'Payment gateway not configured.'], 503);
        }

        try {
            $paymentData = $this->razorpayService->createOrder($order);

            return response()->json([
                'message' => 'Payment initiated.',
                'data' => $paymentData,
                'order' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to initiate payment.'], 500);
        }
    }

    public function verifyPayment(VerifyPaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->razorpayService->verifyPayment($request->validated());

            if ($payment->customer_id !== auth('api')->id()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return response()->json([
                'message' => 'Payment verified successfully.',
                'data' => new PaymentResource($payment),
                'order' => new OrderResource($payment->order->load(['items', 'payment'])),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment verification failed.'], 422);
        }
    }
}
