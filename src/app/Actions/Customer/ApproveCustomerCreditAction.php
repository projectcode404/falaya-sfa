<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class ApproveCustomerCreditAction
{
    public function execute(Customer $customer, ?float $adjustedCreditLimit = null): Customer
    {
        if (! $customer->isPendingApproval()) {
            throw new \LogicException('Customer status bukan PENDING_APPROVAL.');
        }

        return DB::transaction(function () use ($customer, $adjustedCreditLimit) {
            $customer->update([
                'status' => 'ACTIVE',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                // Owner bisa sesuaikan credit_limit sebelum approve
                'credit_limit' => $adjustedCreditLimit ?? $customer->credit_limit,
                'updated_at' => now(),
            ]);

            return $customer->fresh();
        });
    }
}
