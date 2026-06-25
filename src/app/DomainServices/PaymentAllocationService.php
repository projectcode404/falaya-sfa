<?php

namespace App\DomainServices;

use App\Models\Invoice;

class PaymentAllocationService
{
    public function allocateFifo(int $customerId, float $paymentAmount): array
    {
        // Ambil invoice UNPAID/PARTIAL/OVERDUE milik customer
        // urutkan by due_date ASC (FIFO -- terlama/due_date terdekat dulu)
        $invoices = Invoice::where('customer_id', $customerId)
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
            ->orderBy('due_date', 'asc')
            ->get();

        $allocations = [];
        $remaining = $paymentAmount;

        foreach ($invoices as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $allocate = min($remaining, (float) $invoice->remaining_amount);
            $allocations[$invoice->id] = $allocate;
            $remaining -= $allocate;
        }

        return $allocations;
    }

    public function validateAllocationSum(float $totalPayment, array $allocations): bool
    {
        $sum = array_sum($allocations);

        return abs($totalPayment - $sum) < 0.01;
    }
}
