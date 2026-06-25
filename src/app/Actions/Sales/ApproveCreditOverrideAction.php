<?php

namespace App\Actions\Sales;

use App\Models\CreditOverrideRequest;
use Illuminate\Support\Facades\DB;

class ApproveCreditOverrideAction
{
    public function execute(CreditOverrideRequest $overrideRequest, ?string $notes = null): CreditOverrideRequest
    {
        if ($overrideRequest->status !== 'PENDING') {
            throw new \LogicException('Hanya PENDING yang bisa di-approve.');
        }

        return DB::transaction(function () use ($overrideRequest, $notes) {
            $overrideRequest->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            $overrideRequest->salesOrder->update([
                'override_status' => 'APPROVED',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $overrideRequest->fresh();
        });
    }
}
