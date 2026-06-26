<?php

namespace App\Actions\CustomerReturn;

use App\Models\CustomerReturn;

class RejectCustomerReturnAction
{
    public function execute(CustomerReturn $customerReturn, string $notes): void
    {
        $customerReturn->update([
            'status' => 'REJECTED',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'approval_notes' => $notes,
        ]);
    }
}
