<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality',
        'province',
        'region',
        'slug',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // All users belonging to this barangay
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Scope: only active barangays
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}