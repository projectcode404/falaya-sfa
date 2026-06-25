<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'customer_code',
        'customer_name',
        'address',
        'area_id',
        'customer_type',
        'status',
        'latitude',
        'longitude',
        'radius_tolerance_meter',
        'credit_limit',
        'credit_term_days',
        'owner_name',
        'owner_phone',
        'owner_nik',
        'owner_name_ktp',
        'owner_address_ktp',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'approval_notes',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'credit_limit' => 'decimal:2',
            'credit_term_days' => 'integer',
            'radius_tolerance_meter' => 'integer',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'PENDING_APPROVAL';
    }

    public function isCash(): bool
    {
        return $this->customer_type === 'CASH';
    }

    public function isCredit(): bool
    {
        return $this->customer_type === 'CREDIT';
    }
}
