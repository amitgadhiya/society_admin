<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchmanNotification extends Model
{
    protected $table = 'watchman_notifications';

    protected $fillable = [
        'watchman_id',
        'title',
        'body',
        'type',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data'    => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function watchman(): BelongsTo
    {
        return $this->belongsTo(Watchman::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
