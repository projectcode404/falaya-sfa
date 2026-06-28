<?php

namespace App\Actions\Sales;

use App\DomainServices\CreditLimitService;
use App\DomainServices\DocumentNumberService;
use App\DomainServices\StockBalanceService;
use App\DomainServices\StockLedgerService;
use App\Models\Invoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Support\IdempotencyGuard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PostSalesOrderAction
{
    public function __construct(
        private readonly StockBalanceService $stockBalanceService,
        private readonly StockLedgerService $stockLedgerService,
        private readonly CreditLimitService $creditLimitService,
        private readonly DocumentNumberService $documentNumberService,
        private readonly IdempotencyGuard $idempotencyGuard,
    ) {}

    public function execute(SalesOrder $salesOrder): SalesOrder
    {
        // Idempotency check DULUAN -- sebelum isDraft check
        // Supaya retry dari PWA yang SO-nya sudah POSTED tidak throw LogicException
        if ($salesOrder->idempotency_key) {
            $existing = $this->idempotencyGuard->checkOrRegister(
                $salesOrder->idempotency_key,
                SalesOrder::class
            );
            if ($existing instanceof SalesOrder && $existing->status === 'POSTED') {
                return $existing;
            }
        }
        if (! $salesOrder->isDraft()) {
            throw new \LogicException('Hanya DRAFT yang bisa di-POST.');
        }

        return DB::transaction(function () use ($salesOrder) {
            /** @var Collection<int, SalesOrderItem> $items */
            $items = $salesOrder->items()->with('product')->get();
            if ($salesOrder->isCredit()) {
                $customer = $salesOrder->customer;
                if (! $this->creditLimitService->checkSufficientLimit($customer, (float) $salesOrder->total_amount)) {
                    throw new \RuntimeException(
                        'Melebihi credit limit. Ajukan override terlebih dahulu.'
                    );
                }
            }
            $balances = [];
            foreach ($items as $item) {
                $balance = $this->stockBalanceService->lockAndGetBalance(
                    $item->product_id,
                    'SALESMAN',
                    $salesOrder->salesman_id,
                    'GOOD'
                );
                if (! $this->stockBalanceService->validateSufficientStock($balance, (float) $item->qty)) {
                    throw new \RuntimeException(
                        "Stok tidak cukup untuk produk {$item->product->product_name}. ".
                        "Dibutuhkan: {$item->qty}, Tersedia: {$balance->qty}"
                    );
                }
                $balances[$item->product_id] = $balance;
            }
            foreach ($items as $item) {
                $this->stockBalanceService->applyMovement(
                    $balances[$item->product_id],
                    -(float) $item->qty
                );
                $this->stockLedgerService->recordMovement(
                    $item->product,
                    'SALESMAN',
                    $salesOrder->salesman_id,
                    'GOOD',
                    -(float) $item->qty,
                    'SALE',
                    $salesOrder->id
                );
            }
            $salesOrder->update([
                'status' => 'POSTED',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);
            if ($salesOrder->isCredit()) {
                $this->generateInvoice($salesOrder);
            }

            return $salesOrder->fresh();
        });
    }

    private function generateInvoice(SalesOrder $salesOrder): Invoice
    {
        $customer = $salesOrder->customer;
        $invoiceNumber = $this->documentNumberService->generate('INV');

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'sales_order_id' => $salesOrder->id,
            'customer_id' => $salesOrder->customer_id,
            'salesman_id' => $salesOrder->salesman_id,
            'customer_name_snapshot' => $customer->customer_name,
            'customer_address_snapshot' => $customer->address,
            'receiver_name' => $salesOrder->receiver_name ?? '-',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays($customer->credit_term_days)->toDateString(),
            'credit_term_days_snapshot' => $customer->credit_term_days,
            'total_amount' => $salesOrder->total_amount,
            'paid_amount' => 0,
            'remaining_amount' => $salesOrder->total_amount,
            'status' => 'UNPAID',
            'created_at' => now(),
        ]);
    }
}
