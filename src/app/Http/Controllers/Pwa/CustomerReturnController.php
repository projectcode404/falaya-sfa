<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\CustomerReturn\CreateCustomerReturnAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerReturnController extends Controller
{
    public function store(Request $request, CreateCustomerReturnAction $action): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'reason' => ['required', 'in:RUSAK,EXPIRED'],
            'refund_type' => ['required', 'in:CREDIT_NOTE,REFUND'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $return = $action->execute([
                'invoice_id' => $validated['invoice_id'],
                'customer_id' => $validated['customer_id'],
                'salesman_id' => Auth::id(),
                'reason' => $validated['reason'],
                'refund_type' => $validated['refund_type'],
                'items' => $validated['items'],
            ]);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Retur diajukan, menunggu persetujuan Owner.',
            'return_id' => $return->id,
            'document_number' => $return->document_number,
            'status' => $return->status,
            'total_amount' => $return->total_amount,
        ], 201);
    }
}
