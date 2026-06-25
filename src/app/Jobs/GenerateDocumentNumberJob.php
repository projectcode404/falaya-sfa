<?php

namespace App\Jobs;

use App\DomainServices\DocumentNumberService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GenerateDocumentNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(DocumentNumberService $service): void
    {
        $number = DB::transaction(function () use ($service) {
            return $service->generate('SO');
        });

        // Simpan hasil ke cache untuk dicek test
        $existing = Cache::get('generated_numbers', []);
        $existing[] = $number;
        Cache::put('generated_numbers', $existing, 60);
    }
}
