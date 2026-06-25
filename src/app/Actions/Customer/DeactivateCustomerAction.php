<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class DeactivateCustomerAction
{
    public function execute(Customer $customer): Customer
    {
        return DB::transaction(function () use ($customer) {
            $customer->update([
                'status' => 'INACTIVE',
                'updated_at' => now(),
            ]);

            return $customer->fresh();
        });
    }
}
