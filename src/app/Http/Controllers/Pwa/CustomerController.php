<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\Customer\CreateCustomerAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function store(Request $request, CreateCustomerAction $action): JsonResponse
    {
        $validated = $request->validate([
            'customer_code' => ['required', 'string', 'max:30', 'unique:customers,customer_code'],
            'customer_name' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string'],
            'area_id' => ['required', 'integer', 'exists:areas,id'],
            'customer_type' => ['required', 'in:CASH,CREDIT'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'radius_tolerance_meter' => ['nullable', 'integer', 'min:10'],
            'owner_name' => ['nullable', 'string', 'max:100'],
            'owner_phone' => ['nullable', 'string', 'max:20'],
            'owner_nik' => ['nullable', 'string', 'max:20'],
            'owner_name_ktp' => ['nullable', 'string', 'max:100'],
            'owner_address_ktp' => ['nullable', 'string'],
            // Wajib hanya untuk CREDIT
            'credit_limit' => ['required_if:customer_type,CREDIT', 'nullable', 'numeric', 'min:0'],
            'credit_term_days' => ['required_if:customer_type,CREDIT', 'nullable', 'integer', 'in:7,14,30'],
        ]);

        $customer = $action->execute($validated);

        $isCash = $customer->customer_type === 'CASH';

        return response()->json([
            'message' => $isCash
                ? 'Outlet berhasil ditambahkan dan langsung aktif.'
                : 'Outlet tersimpan, menunggu persetujuan Owner sebelum bisa transaksi kredit.',
            'customer_id' => $customer->id,
            'status' => $customer->status,
        ], 201);
    }
}
