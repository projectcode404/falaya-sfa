<?php

namespace App\Actions\Collection;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\DomainServices\PaymentAllocationService;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PostPaymentAction
{
    public function __construct(
        private readonly PaymentAllocationService $paymentAllocationService,
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(Payment $payment): Payment
    {
        if (! $payment->isDraft()) {
            throw new \LogicException('Hanya DRAFT yang bisa di-POST.');
        }

        return DB::transaction(function () use ($payment) {
            $allocations = $payment->allocations;

            // Validasi SUM(allocation) == total_amount
            $allocationSum = $allocations->sum('allocated_amount');
            if (abs((float) $payment->total_amount - (float) $allocationSum) > 0.01) {
                throw new \RuntimeException(
                    'Total alokasi tidak sama dengan total pembayaran.'
                );
            }

            // Update setiap invoice
            $totalRemaining = 0;
            foreach ($allocations as $allocation) {
                $invoice = Invoice::lockForUpdate()->find($allocation->invoice_id);

                $newPaidAmount = (float) $invoice->paid_amount + (float) $allocation->allocated_amount;
                $newRemainingAmount = (float) $invoice->total_amount - $newPaidAmount;

                $status = $newRemainingAmount <= 0 ? 'PAID'
                    : ($newPaidAmount > 0 ? 'PARTIAL' : $invoice->status);

                $invoice->update([
                    'paid_amount' => $newPaidAmount,
                    'remaining_amount' => max(0, $newRemainingAmount),
                    'status' => $status,
                ]);

                $totalRemaining += max(0, $newRemainingAmount);
            }

            $payment->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // Generate receipt
            $receiptNumber = $this->documentNumberService->generate('RCP');
            $collector = User::find($payment->collected_by);

            PaymentReceipt::create([
                'receipt_number' => $receiptNumber,
                'payment_id' => $payment->id,
                'customer_id' => $payment->customer_id,
                'customer_name_snapshot' => $payment->customer->customer_name,
                'collector_name_snapshot' => $collector?->name ?? '-',
                'total_paid' => $payment->total_amount,
                'remaining_after' => $totalRemaining,
                'receipt_date' => $this->operationalDateService->current()->toDateString(),
                'qr_payload' => route('receipts.verify', ['receipt' => $receiptNumber]),
                'status' => 'POSTED',
                'created_at' => now(),
            ]);

            return $payment->fresh();
        });
    }
}
