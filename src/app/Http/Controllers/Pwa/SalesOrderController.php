<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\Sales\CancelSalesOrderAction;
use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Actions\Sales\RequestCreditOverrideAction;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesOrderController extends Controller
{
    public function store(Request $request, CreateSalesOrderAction $action): JsonResponse
    {
        $validated = $request->validate([
            'visit_plan_id' => ['required', 'integer', 'exists:visit_plans,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'payment_type' => ['required', 'in:CASH,CREDIT'],
            'receiver_name' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $salesOrder = $action->execute(
                $validated['visit_plan_id'],
                $validated['customer_id'],
                Auth::id(),
                $validated['payment_type'],
                $validated['items'],
                $validated['receiver_name'] ?? null,
            );
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Sales Order berhasil dibuat.',
            'sales_order_id' => $salesOrder->id,
            'document_number' => $salesOrder->document_number,
            'status' => $salesOrder->status,
            'total_amount' => $salesOrder->total_amount,
        ], 201);
    }

    public function post(
        Request $request,
        SalesOrder $salesOrder,
        PostSalesOrderAction $postAction,
        RequestCreditOverrideAction $overrideAction
    ): JsonResponse {
        if ($salesOrder->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $salesOrder = $postAction->execute($salesOrder);
        } catch (\RuntimeException $e) {
            // Credit limit melebihi — tawarkan override
            if (str_contains($e->getMessage(), 'credit limit')) {
                try {
                    $override = $overrideAction->execute($salesOrder->fresh());
                } catch (\LogicException $le) {
                    return response()->json(['message' => $le->getMessage()], 409);
                }

                return response()->json([
                    'message' => 'Melebihi credit limit. Override diminta ke Owner.',
                    'requires_override' => true,
                    'override_request_id' => $override->id,
                    'sales_order_id' => $salesOrder->id,
                ], 422);
            }

            // Stok tidak cukup — permanent error
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Sales Order berhasil di-POST.',
            'sales_order_id' => $salesOrder->id,
            'document_number' => $salesOrder->document_number,
            'status' => $salesOrder->status,
            'invoice_id' => $salesOrder->invoice?->id,
        ]);
    }

    public function cancel(Request $request, SalesOrder $salesOrder, CancelSalesOrderAction $action): JsonResponse
    {
        if ($salesOrder->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        try {
            $salesOrder = $action->execute($salesOrder, $validated['reason']);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Sales Order berhasil di-cancel.',
            'sales_order_id' => $salesOrder->id,
            'status' => $salesOrder->status,
        ]);
    }
}
