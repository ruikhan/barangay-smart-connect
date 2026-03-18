<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'barangay_id',
        'user_id',
        'title',
        'content',
        'category',
        'priority',
        'is_pinned',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_pinned'    => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent'    => 'error',
            'important' => 'warning',
            default     => 'primary',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            'emergency'      => 'mdi-alert-circle',
            'health'         => 'mdi-medical-bag',
            'infrastructure' => 'mdi-road-variant',
            'event'          => 'mdi-calendar-star',
            'livelihood'     => 'mdi-briefcase-outline',
            default          => 'mdi-bullhorn-outline',
        };
    }
}