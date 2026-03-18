<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'barangay_id',
        'type',
        'status',
        'purpose',
        'reference_number',
        'remarks',
        'processed_at',
        'approved_at',
        'released_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'approved_at'  => 'datetime',
        'released_at'  => 'datetime',
    ];

    // Auto-generate reference number
    protected static function booted(): void
    {
        static::creating(function ($request) {
            $request->reference_number = 'BSC-' .
                strtoupper(substr($request->type, 0, 3)) . '-' .
                date('Ymd') . '-' .
                strtoupper(Str::random(5));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    // Status label helpers
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'warning',
            'processing' => 'info',
            'approved'   => 'success',
            'released'   => 'success',
            'rejected'   => 'error',
            default      => 'grey',
        };
    }
}