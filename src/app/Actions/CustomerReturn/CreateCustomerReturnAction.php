<?php

namespace App\Actions\CustomerReturn;

use App\DomainServices\DocumentNumberService;
use App\Models\CustomerReturn;
use App\Models\CustomerReturnItem;
use Illuminate\Support\Facades\DB;

class CreateCustomerReturnAction
{
    public function __construct(
        private readonly DocumentNumberService $documentNumberService,
    ) {}

    public function execute(array $data): CustomerReturn
    {
        return DB::transaction(function () use ($data) {
            $documentNumber = $this->documentNumberService->generate('CR');

            $totalAmount = collect($data['items'])->sum(
                fn ($item) => $item['qty'] * $item['unit_price']
            );

            $customerReturn = CustomerReturn::create([
                'document_number' => $documentNumber,
                'invoice_id' => $data['invoice_id'],
                'customer_id' => $data['customer_id'],
                'salesman_id' => $data['salesman_id'],
                'reason' => $data['reason'],
                'total_amount' => $totalAmount,
                'refund_type' => $data['refund_type'],
                'status' => 'PENDING_APPROVAL',
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            foreach ($data['items'] as $item) {
                CustomerReturnItem::create([
                    'customer_return_id' => $customerReturn->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $customerReturn->load('items');
        });
    }
}
