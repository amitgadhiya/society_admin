<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['society_id', 'name', 'mobile', 'photo', 'employee_id', 'active', 'password', 'fcm_token'])]
class Watchman extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $hidden = ['password'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function watchmanTasks(): HasMany
    {
        return $this->hasMany(WatchmanTask::class);
    }
}
