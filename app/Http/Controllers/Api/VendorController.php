<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repositories\OrderRepository;
use App\Services\VendorOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected VendorOrderService $vendorOrderService,
    ) {}

    public function orders(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->forVendor(
            auth('api')->id(),
            (int) $request->get('per_page', 15),
            $request->get('filter')
        );

        return OrderResource::collection($orders)->response();
    }

    public function showOrder(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order || $order->vendor_id !== auth('api')->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json(['data' => new OrderResource($order)]);
    }

    public function markPacked(int $id): JsonResponse
    {
        $order = Order::query()->find($id);

        if (! $order || $order->vendor_id !== auth('api')->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        try {
            $order = $this->vendorOrderService->markPacked($order);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Order marked as packed.',
            'data' => new OrderResource($order),
        ]);
    }
}
