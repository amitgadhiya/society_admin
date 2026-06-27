<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'society_id',
    'mode',
    'default_amount',
    'effective_from',
    'status',
    'created_by',
])]
class MaintenancePlan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'default_amount' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(MaintenancePlanRate::class);
    }
}
