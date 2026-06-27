<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'society_id',
        'title',
        'description',
        'status',
        'is_repetitive',
        'deadline_date',
        'days_to_complete',
        'recurrence_type',
        'recurrence_ends',
        'occurrences',
        'end_date',
        'week_days',
        'month_day',
        'months',
        'scheduled_time',
    ];

    protected $casts = [
        'is_repetitive' => 'boolean',
        'deadline_date' => 'date',
        'end_date'      => 'date',
        'week_days'     => 'array',
        'months'        => 'array',
    ];

    public function watchmanTasks(): HasMany
    {
        return $this->hasMany(WatchmanTask::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WatchmanTaskLog::class);
    }
}
