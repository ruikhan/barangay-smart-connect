<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'body',
        'icon', 'color', 'data', 'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Static helper to create notifications easily ──────
    public static function notify(int $userId, string $type, string $title, string $body = null, array $extra = []): self
    {
        $icons = [
            'request_update' => 'mdi-file-check-outline',
            'announcement'   => 'mdi-bullhorn-outline',
            'message'        => 'mdi-message-outline',
            'system'         => 'mdi-information-outline',
        ];

        $colors = [
            'request_update' => 'primary',
            'announcement'   => 'warning',
            'message'        => 'success',
            'system'         => 'info',
        ];

        return self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'icon'    => $icons[$type]  ?? 'mdi-bell-outline',
            'color'   => $colors[$type] ?? 'primary',
            'data'    => $extra ?: null,
            'is_read' => false,
        ]);
    }
}
