<?php

namespace App\Http\Controllers\Pwa;

use App\Actions\Visit\CheckinVisitAction;
use App\Actions\Visit\CheckoutVisitAction;
use App\Actions\Visit\CreateUnplannedVisitAction;
use App\DomainServices\OperationalDateService;
use App\Http\Controllers\Controller;
use App\Models\VisitPlan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisitController extends Controller
{
    public function checkin(Request $request, CheckinVisitAction $action): JsonResponse
    {
        $validated = $request->validate([
            'visit_plan_id' => ['required', 'integer', 'exists:visit_plans,id'],
            'idempotency_key' => ['required', 'uuid'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'gps_unavailable' => ['boolean'],
        ]);
        $visitPlan = VisitPlan::findOrFail($validated['visit_plan_id']);
        if ($visitPlan->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        $gpsData = [
            'unavailable' => $validated['gps_unavailable'] ?? false,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'accuracy' => $validated['accuracy'] ?? null,
        ];
        try {
            $realization = $action->execute($visitPlan, $gpsData, $validated['idempotency_key']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
        // 201 = baru dibuat, 200 = idempotent (record existing dikembalikan)
        $statusCode = $realization->wasRecentlyCreated ? 201 : 200;

        return response()->json([
            'message' => 'Check-in berhasil.',
            'realization_id' => $realization->id,
            'visit_plan_id' => $visitPlan->id,
            'checkin_at' => $realization->checkin_at,
            'gps_unavailable' => $realization->gps_unavailable,
            'gps_low_accuracy' => $realization->gps_low_accuracy,
        ], $statusCode);
    }

    public function checkout(Request $request, CheckoutVisitAction $action): JsonResponse
    {
        $validated = $request->validate([
            'visit_plan_id' => ['required', 'integer', 'exists:visit_plans,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);
        $visitPlan = VisitPlan::findOrFail($validated['visit_plan_id']);
        if ($visitPlan->salesman_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        $gpsData = [
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ];
        try {
            $realization = $action->execute($visitPlan, $gpsData);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Check-out berhasil.',
            'realization_id' => $realization->id,
            'visit_plan_id' => $visitPlan->id,
            'checkout_at' => $realization->checkout_at,
            'visit_status' => $visitPlan->fresh()->status,
        ]);
    }

    public function createUnplanned(
        Request $request,
        CreateUnplannedVisitAction $action,
        OperationalDateService $operationalDateService
    ): JsonResponse {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
        ]);
        $operationalDate = $operationalDateService->current()->toDateString();
        try {
            $visitPlan = $action->execute(
                Auth::id(),
                $validated['customer_id'],
                $operationalDate
            );
        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'message' => 'Kunjungan ke outlet ini sudah ada untuk hari ini.',
            ], 409);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json([
            'message' => 'Kunjungan tidak terjadwal berhasil dibuat.',
            'visit_plan_id' => $visitPlan->id,
        ], 201);
    }
}
