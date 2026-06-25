<?php

namespace App\DomainServices;

use App\Models\StockBalance;

class StockBalanceService
{
    public function lockAndGetBalance(
        int $productId,
        string $holderType,
        ?int $holderId,
        string $condition
    ): StockBalance {
        // SELECT ... FOR UPDATE -- wajib dipanggil di dalam transaction aktif
        return StockBalance::where('product_id', $productId)
            ->where('holder_type', $holderType)
            ->where('holder_id', $holderId)
            ->where('condition', $condition)
            ->lockForUpdate()
            ->firstOrCreate(
                [
                    'product_id' => $productId,
                    'holder_type' => $holderType,
                    'holder_id' => $holderId,
                    'condition' => $condition,
                ],
                ['qty' => 0]
            );
    }

    public function validateSufficientStock(
        StockBalance $balance,
        float $requiredQty
    ): bool {
        // Menerima StockBalance yang SUDAH di-lock, bukan query baru
        // mencegah TOCTOU (time-of-check-time-of-use) bug
        return $balance->qty >= $requiredQty;
    }

    public function applyMovement(StockBalance $balance, float $qtyDelta): void
    {
        // UPDATE pada baris yang SUDAH di-lock
        // qtyDelta positif = masuk, negatif = keluar
        $balance->update([
            'qty' => $balance->qty + $qtyDelta,
            'updated_at' => now(),
        ]);
    }
}
