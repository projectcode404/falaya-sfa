<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'current_session_token',
    ];

    protected $hidden = [
        'password',
        'current_session_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function salesmanAreas()
    {
        return $this->hasMany(SalesmanArea::class);
    }

    public function activeAreas()
    {
        return $this->hasMany(SalesmanArea::class)->where('is_active', true);
    }

    public function isOwner(): bool
    {
        return $this->role === 'OWNER';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isSalesman(): bool
    {
        return $this->role === 'SALESMAN';
    }

    public function cashReconciliations()
    {
        return $this->hasMany(DailyCashReconciliation::class, 'salesman_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'collected_by');
    }
}
