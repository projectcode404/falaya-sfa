<?php

namespace App\Actions\Collection;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;

class CreatePaymentAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(
        int $customerId,
        float $totalAmount,
        string $paymentMethod,
        array $allocations,
        ?int $visitPlanId = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($customerId, $totalAmount, $paymentMethod, $allocations, $visitPlanId, $notes) {
            $paymentNumber = $this->documentNumberService->generate('PAY');

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'customer_id' => $customerId,
                'collected_by' => auth()->id(),
                'visit_plan_id' => $visitPlanId,
                'operational_date' => $this->operationalDateService->current()->toDateString(),
                'payment_method' => $paymentMethod,
                'total_amount' => $totalAmount,
                'notes' => $notes,
                'status' => 'DRAFT',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            foreach ($allocations as $invoiceId => $allocatedAmount) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoiceId,
                    'allocated_amount' => $allocatedAmount,
                ]);
            }

            return $payment->load('allocations');
        });
    }
}
