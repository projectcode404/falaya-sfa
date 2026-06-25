<?php

namespace App\Actions\Sales;

use App\DomainServices\DocumentNumberService;
use App\DomainServices\OperationalDateService;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;

class CreateSalesOrderAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
        private readonly OperationalDateService $operationalDateService,
    ) {}

    public function execute(
        int $visitPlanId,
        int $customerId,
        int $salesmanId,
        string $paymentType,
        array $items,
        ?string $receiverName = null
    ): SalesOrder {
        return DB::transaction(function () use ($visitPlanId, $customerId, $salesmanId, $paymentType, $items, $receiverName) {
            $documentNumber = $this->documentNumberService->generate('SO');

            $subtotal = collect($items)->sum(fn ($item) => $item['qty'] * $item['unit_price']);

            $salesOrder = SalesOrder::create([
                'document_number' => $documentNumber,
                'visit_plan_id' => $visitPlanId,
                'customer_id' => $customerId,
                'salesman_id' => $salesmanId,
                'operational_date' => $this->operationalDateService->current()->toDateString(),
                'payment_type' => $paymentType,
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'status' => 'DRAFT',
                'receiver_name' => $receiverName,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            foreach ($items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['qty'] * $item['unit_price'],
                ]);
            }

            return $salesOrder->load('items');
        });
    }
}
