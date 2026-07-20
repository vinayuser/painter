<?php

namespace App\Http\Controllers\Api;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\CompleteDeliveryRequest;
use App\Http\Requests\Delivery\UpdateDeliveryStatusRequest;
use App\Http\Resources\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected DeliveryService $deliveryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderRepository->forDeliveryAgent(
            auth('api')->id(),
            (int) $request->get('per_page', 15),
            $request->get('filter')
        );

        return OrderResource::collection($orders)->response();
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

    public function accept(int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order || $order->delivery_agent_id !== auth('api')->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order = $this->deliveryService->acceptDelivery($order);

        return response()->json([
            'message' => 'Delivery accepted.',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateDeliveryStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $this->authorize('updateDelivery', $order);

        $status = DeliveryStatus::from($request->string('status')->toString());
        $order = $this->deliveryService->updateStatus($order, $status);

        if ($request->hasFile('delivery_proof')) {
            $order = $this->deliveryService->uploadProof($order, $request->file('delivery_proof'));
        }

        return response()->json([
            'message' => 'Delivery status updated.',
            'data' => new OrderResource($order),
        ]);
    }

    public function complete(CompleteDeliveryRequest $request, int $id): JsonResponse
    {
        $order = $this->orderRepository->findWithRelations($id);

        if (! $order || $order->delivery_agent_id !== auth('api')->id()) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        $order = $this->deliveryService->uploadProof($order, $request->file('delivery_proof'));
        $order = $this->deliveryService->updateStatus($order, DeliveryStatus::Delivered);

        return response()->json([
            'message' => 'Order delivered successfully.',
            'data' => new OrderResource($order),
        ]);
    }
}
