<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchmanTaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'watchman_id', 'completion_date','photo','latitude','longitude',
        'is_completed', 'completed_at', 'remarks',
    ];

    protected $casts = [
        'is_completed'    => 'boolean',
        'completed_at'    => 'datetime',
        'completion_date' => 'date',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function watchman(): BelongsTo
    {
        return $this->belongsTo(Watchman::class);
    }
}
