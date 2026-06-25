<?php

namespace App\Actions\Sales;

use App\DomainServices\CreditLimitService;
use App\Models\CreditOverrideRequest;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class RequestCreditOverrideAction
{
    public function __construct(
        private readonly CreditLimitService $creditLimitService,
    ) {}

    public function execute(SalesOrder $salesOrder): CreditOverrideRequest
    {
        if (! $salesOrder->isDraft()) {
            throw new \LogicException('Override hanya bisa diminta untuk Sales Order DRAFT.');
        }

        if (! $salesOrder->isCredit()) {
            throw new \LogicException('Override hanya berlaku untuk Sales Order CREDIT.');
        }

        return DB::transaction(function () use ($salesOrder) {
            $customer = $salesOrder->customer;
            $outstanding = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->sum('remaining_amount');

            $overrideRequest = CreditOverrideRequest::create([
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'outstanding_at_request' => $outstanding,
                'order_amount' => $salesOrder->total_amount,
                'credit_limit_at_request' => $customer->credit_limit,
                'status' => 'PENDING',
                'requested_by' => auth()->id(),
                'requested_at' => now(),
            ]);

            $salesOrder->update([
                'requires_override' => true,
                'override_status' => 'PENDING',
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            return $overrideRequest;
        });
    }
}
