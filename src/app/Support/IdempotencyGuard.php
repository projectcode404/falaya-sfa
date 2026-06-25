<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class IdempotencyGuard
{
    public function checkOrRegister(string $key, string $modelClass): ?Model
    {
        // Cek apakah idempotency_key sudah pernah diproses
        $existing = $modelClass::where('idempotency_key', $key)->first();

        if ($existing) {
            // Return hasil yang SAMA, bukan error -- idempotent
            return $existing;
        }

        // Belum pernah diproses, lanjutkan normal
        return null;
    }
}
