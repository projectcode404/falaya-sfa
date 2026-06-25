<?php

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class RejectCustomerCreditAction
{
    public function execute(Customer $customer, string $reason): Customer
    {
        if (! $customer->isPendingApproval()) {
            throw new \LogicException('Customer status bukan PENDING_APPROVAL.');
        }

        return DB::transaction(function () use ($customer, $reason) {
            $customer->update([
                'status' => 'REJECTED',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'approval_notes' => $reason,
                'updated_at' => now(),
            ]);

            return $customer->fresh();
        });
    }
}
