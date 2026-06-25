<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\Collection\CreatePaymentAction;
use App\Actions\Collection\PostPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\CollectionTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function recordCashPayment(
        Request $request,
        CreatePaymentAction $createAction,
        PostPaymentAction $postAction
    ): JsonResponse {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'visit_plan_id' => ['nullable', 'integer', 'exists:visit_plans,id'],
            'total_amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:500'],
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.invoice_id' => ['required', 'integer', 'exists:invoices,id'],
            'allocations.*.amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        // Konversi array ke format keyed by invoice_id
        $allocations = collect($validated['allocations'])
            ->mapWithKeys(fn ($item) => [$item['invoice_id'] => $item['amount']])
            ->toArray();

        try {
            $payment = $createAction->execute(
                $validated['customer_id'],
                $validated['total_amount'],
                'CASH',
                $allocations,
                $validated['visit_plan_id'] ?? null,
                $validated['notes'] ?? null,
            );

            $payment = $postAction->execute($payment);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $receipt = $payment->fresh()->receipt;

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'receipt_id' => $receipt?->id,
            'receipt_number' => $receipt?->receipt_number,
        ], 201);
    }

    public function skip(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collection_task_id' => ['required', 'integer', 'exists:collection_tasks,id'],
            'reason' => ['required', 'in:no_money,reschedule,already_transferred,other'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $task = CollectionTask::findOrFail($validated['collection_task_id']);

        if ($task->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $status = $validated['reason'] === 'reschedule' ? 'RESCHEDULED' : 'NO_PAYMENT';

        $task->update([
            'status' => $status,
            'result_notes' => $validated['notes'] ?? $validated['reason'],
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Penagihan dilewati.',
            'status' => $task->status,
        ]);
    }
}
