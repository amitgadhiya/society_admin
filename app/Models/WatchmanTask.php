<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchmanTask extends Model
{
    use HasFactory;

    protected $fillable = ['task_id', 'watchman_id', 'status'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function watchman(): BelongsTo
    {
        return $this->belongsTo(Watchman::class);
    }
}
