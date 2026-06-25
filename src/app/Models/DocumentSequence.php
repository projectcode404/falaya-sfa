<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_type',
        'operational_date',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'operational_date' => 'date',
            'last_number' => 'integer',
        ];
    }
}
