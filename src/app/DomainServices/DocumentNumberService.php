<?php

namespace App\DomainServices;

use App\Models\DocumentSequence;

class DocumentNumberService
{
    public function __construct(
        private readonly OperationalDateService $operationalDateService
    ) {}

    public function generate(string $documentType): string
    {
        // WAJIB dipanggil dari dalam DB::transaction() milik Action pemanggil
        $date = $this->operationalDateService->current();
        $dateString = $date->format('ymd'); // YYMMDD

        $sequence = DocumentSequence::where('document_type', $documentType)
            ->where('operational_date', $date->toDateString())
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $sequence = DocumentSequence::create([
                'document_type' => $documentType,
                'operational_date' => $date->toDateString(),
                'last_number' => 0,
            ]);
        }

        $sequence->increment('last_number');
        $sequence->refresh();

        $number = str_pad($sequence->last_number, 4, '0', STR_PAD_LEFT);

        return "{$documentType}-{$dateString}-{$number}";
    }
}
