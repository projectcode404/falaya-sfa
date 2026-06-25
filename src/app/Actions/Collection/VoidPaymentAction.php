<?php

namespace App\Actions\Collection;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class VoidPaymentAction
{
    public function execute(Payment $payment, string $reason): Payment
    {
        if (! $payment->isPosted()) {
            throw new \LogicException('Hanya POSTED yang bisa di-void.');
        }

        return DB::transaction(function () use ($payment, $reason) {
            // Reversal alokasi -- kembalikan invoice ke status sebelumnya
            foreach ($payment->allocations as $allocation) {
                $invoice = Invoice::lockForUpdate()->find($allocation->invoice_id);

                $newPaidAmount = max(0, (float) $invoice->paid_amount - (float) $allocation->allocated_amount);
                $newRemainingAmount = (float) $invoice->total_amount - $newPaidAmount;

                $status = $newPaidAmount <= 0 ? 'UNPAID'
                    : ($newRemainingAmount <= 0 ? 'PAID' : 'PARTIAL');

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => $newRemainingAmount,
                    'status' => $status,
                ]);
            }

            // Void receipt
            if ($payment->receipt) {
                $payment->receipt->update(['status' => 'VOID']);
            }

            $payment->update([
                'status' => 'VOID',
                'void_by' => auth()->id(),
                'void_at' => now(),
                'void_reason' => $reason,
            ]);

            return $payment->fresh();
        });
    }
}
