<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\CustomerAddressResource;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService,
    ) {}

    public function index(): JsonResponse
    {
        $addresses = $this->addressService->listForUser(auth('api')->id());

        return response()->json([
            'data' => CustomerAddressResource::collection($addresses),
        ]);
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addressService->create(
            auth('api')->id(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Address saved successfully.',
            'data' => new CustomerAddressResource($address),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $address = $this->addressService->listForUser(auth('api')->id())
            ->firstWhere('id', $id);

        if (! $address) {
            return response()->json(['message' => 'Address not found.'], 404);
        }

        return response()->json([
            'data' => new CustomerAddressResource($address),
        ]);
    }

    public function update(UpdateAddressRequest $request, int $id): JsonResponse
    {
        try {
            $address = $this->addressService->update(
                auth('api')->id(),
                $id,
                $request->validated()
            );

            return response()->json([
                'message' => 'Address updated successfully.',
                'data' => new CustomerAddressResource($address),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->addressService->delete(auth('api')->id(), $id);

            return response()->json(['message' => 'Address deleted successfully.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function setDefault(int $id): JsonResponse
    {
        try {
            $address = $this->addressService->setDefault(auth('api')->id(), $id);

            return response()->json([
                'message' => 'Default address updated.',
                'data' => new CustomerAddressResource($address),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
