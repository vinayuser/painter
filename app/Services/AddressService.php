<?php

namespace App\Services;

use App\Models\CustomerAddress;
use App\Repositories\AddressRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function __construct(
        protected AddressRepository $addressRepository,
    ) {}

    public function listForUser(int $userId): Collection
    {
        return $this->addressRepository->forUser($userId);
    }

    public function create(int $userId, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($userId, $data): CustomerAddress {
            $isFirstAddress = $this->addressRepository->countForUser($userId) === 0;
            $setAsDefault = $isFirstAddress || ($data['is_default'] ?? false);

            if ($setAsDefault) {
                $this->addressRepository->clearDefaultForUser($userId);
            }

            return $this->addressRepository->create([
                'user_id' => $userId,
                'label' => $data['label'] ?? 'Home',
                'recipient_name' => $data['recipient_name'],
                'phone' => $data['phone'],
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'is_default' => $setAsDefault,
            ]);
        });
    }

    public function update(int $userId, int $addressId, array $data): CustomerAddress
    {
        return DB::transaction(function () use ($userId, $addressId, $data): CustomerAddress {
            $address = $this->addressRepository->findForUser($userId, $addressId);

            if (! $address) {
                throw new \RuntimeException('Address not found.');
            }

            if (($data['is_default'] ?? false) && ! $address->is_default) {
                $this->addressRepository->clearDefaultForUser($userId);
            }

            $this->addressRepository->update($address, [
                'label' => $data['label'] ?? $address->label,
                'recipient_name' => $data['recipient_name'] ?? $address->recipient_name,
                'phone' => $data['phone'] ?? $address->phone,
                'address_line1' => $data['address_line1'] ?? $address->address_line1,
                'address_line2' => array_key_exists('address_line2', $data) ? $data['address_line2'] : $address->address_line2,
                'city' => $data['city'] ?? $address->city,
                'state' => $data['state'] ?? $address->state,
                'pincode' => $data['pincode'] ?? $address->pincode,
                'is_default' => ($data['is_default'] ?? false) ? true : $address->is_default,
            ]);

            return $address->fresh();
        });
    }

    public function delete(int $userId, int $addressId): void
    {
        DB::transaction(function () use ($userId, $addressId): void {
            $address = $this->addressRepository->findForUser($userId, $addressId);

            if (! $address) {
                throw new \RuntimeException('Address not found.');
            }

            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $nextDefault = $this->addressRepository->forUser($userId)->first();

                if ($nextDefault) {
                    $this->addressRepository->update($nextDefault, ['is_default' => true]);
                }
            }
        });
    }

    public function setDefault(int $userId, int $addressId): CustomerAddress
    {
        return DB::transaction(function () use ($userId, $addressId): CustomerAddress {
            $address = $this->addressRepository->findForUser($userId, $addressId);

            if (! $address) {
                throw new \RuntimeException('Address not found.');
            }

            $this->addressRepository->clearDefaultForUser($userId);
            $this->addressRepository->update($address, ['is_default' => true]);

            return $address->fresh();
        });
    }

    /**
     * @return array{shipping_address: string, shipping_phone: ?string}
     */
    public function resolveShippingDetails(int $userId, array $data): array
    {
        if (! empty($data['address_id'])) {
            $address = $this->addressRepository->findForUser($userId, (int) $data['address_id']);

            if (! $address) {
                throw new \RuntimeException('Delivery address not found.');
            }

            return [
                'shipping_address' => $address->formatShippingAddress(),
                'shipping_phone' => $data['shipping_phone'] ?? $address->phone,
            ];
        }

        if (empty($data['shipping_address'])) {
            throw new \RuntimeException('Please provide a delivery address or select a saved address.');
        }

        return [
            'shipping_address' => $data['shipping_address'],
            'shipping_phone' => $data['shipping_phone'] ?? null,
        ];
    }
}
