<?php

namespace App\DomainServices;

use App\Models\Customer;
use App\Models\Invoice;

class CreditLimitService
{
    public function checkSufficientLimit(Customer $customer, float $newOrderAmount): bool
    {
        $outstanding = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('remaining_amount');

        return ($outstanding + $newOrderAmount) <= $customer->credit_limit;
    }

    public function getRemainingLimit(Customer $customer): float
    {
        $outstanding = Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->sum('remaining_amount');

        return $customer->credit_limit - $outstanding;
    }
}
