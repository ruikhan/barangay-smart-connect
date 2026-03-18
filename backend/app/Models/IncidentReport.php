<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'barangay_id', 'title', 'description',
        'category', 'severity', 'latitude', 'longitude',
        'address', 'status',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low'      => 'success',
            'moderate' => 'warning',
            'high'     => 'error',
            'critical' => 'error',
            default    => 'grey',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'flood'          => 'mdi-water-alert',
            'fire'           => 'mdi-fire',
            'accident'       => 'mdi-car-emergency',
            'crime'          => 'mdi-shield-alert',
            'infrastructure' => 'mdi-road-variant',
            'health'         => 'mdi-medical-bag',
            default          => 'mdi-alert-circle',
        };
    }
}