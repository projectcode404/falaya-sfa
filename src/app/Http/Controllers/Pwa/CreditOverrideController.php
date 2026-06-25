<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\Sales\RequestCreditOverrideAction;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditOverrideController extends Controller
{
    public function request(Request $request, RequestCreditOverrideAction $action): JsonResponse
    {
        $validated = $request->validate([
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
        ]);

        $salesOrder = SalesOrder::findOrFail($validated['sales_order_id']);

        if ($salesOrder->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $override = $action->execute($salesOrder);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Persetujuan override diminta ke Owner.',
            'override_request_id' => $override->id,
            'status' => $override->status,
        ], 201);
    }

    public function checkStatus(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $override = $salesOrder->creditOverrideRequest;

        if (! $override) {
            return response()->json(['message' => 'Tidak ada permintaan override untuk Sales Order ini.'], 404);
        }

        return response()->json([
            'sales_order_id' => $salesOrder->id,
            'override_status' => $override->status,
            'approval_notes' => $override->approval_notes,
            'can_post' => $override->status === 'APPROVED',
        ]);
    }
}
