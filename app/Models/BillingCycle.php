<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['society_id', 'title', 'month', 'year', 'due_date', 'status'])]
class BillingCycle extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function maintenanceBills(): HasMany
    {
        return $this->hasMany(MaintenanceBill::class);
    }
}
