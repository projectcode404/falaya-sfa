<?php

namespace App\Actions\CustomerReturn;

use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ApproveCustomerReturnAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
    ) {}

    public function execute(CustomerReturn $customerReturn, ?string $notes = null): CustomerReturn
    {
        if (! $customerReturn->isPendingApproval()) {
            throw new \LogicException('Hanya PENDING_APPROVAL yang bisa di-approve.');
        }

        return DB::transaction(function () use ($customerReturn, $notes) {
            /** @var Collection<int, CustomerReturnItem> $items */
            $items = $customerReturn->items()->with('product')->get();

            foreach ($items as $item) {
                // Masuk ke Gudang BAD langsung
                $warehouseBadBalance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'WAREHOUSE',
                    null,
                    'BAD'
                );
                $this->stockBalanceService->applyMovement($warehouseBadBalance, (float) $item->qty);

                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'WAREHOUSE',
                    null,
                    'BAD',
                    (float) $item->qty,
                    'CUSTOMER_RETURN',
                    $customerReturn->id,
                    null,
                    $customerReturn->reason
                );
            }

            // Update invoice outstanding
            $invoice = $customerReturn->invoice;
            $newPaidAmount = (float) $invoice->paid_amount + (float) $customerReturn->total_amount;
            $newRemainingAmount = (float) $invoice->total_amount - $newPaidAmount;

            $invoiceStatus = $newRemainingAmount <= 0 ? 'PAID'
                : ($newPaidAmount > 0 ? 'PARTIAL' : $invoice->status);

            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'remaining_amount' => max(0, $newRemainingAmount),
                'status' => $invoiceStatus,
            ]);

            $customerReturn->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $notes,
            ]);

            return $customerReturn->fresh();
        });
    }
}
