<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesmanArea extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'area_id',
        'effective_from',
        'effective_to',
        'is_active',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
