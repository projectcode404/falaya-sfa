<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CreateCustomerAction
{
    public function execute(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $isCash = $data['customer_type'] === 'CASH';

            return Customer::create([
                'customer_code' => $data['customer_code'],
                'customer_name' => $data['customer_name'],
                'address' => $data['address'],
                'area_id' => $data['area_id'],
                'customer_type' => $data['customer_type'],
                // CASH langsung ACTIVE, CREDIT menunggu approval Owner
                'status' => $isCash ? 'ACTIVE' : 'PENDING_APPROVAL',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius_tolerance_meter' => $data['radius_tolerance_meter'] ?? null,
                // Credit fields -- hanya untuk CREDIT
                'credit_limit' => $isCash ? null : $data['credit_limit'],
                'credit_term_days' => $isCash ? null : $data['credit_term_days'],
                // Data pemilik
                'owner_name' => $data['owner_name'] ?? null,
                'owner_phone' => $data['owner_phone'] ?? null,
                'owner_nik' => $data['owner_nik'] ?? null,
                'owner_name_ktp' => $data['owner_name_ktp'] ?? null,
                'owner_address_ktp' => $data['owner_address_ktp'] ?? null,
                'requested_by' => auth()->id(),
                'created_at' => now(),
            ]);
        });
    }
}
