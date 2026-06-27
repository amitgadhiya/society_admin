<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['society_id', 'name', 'mobile', 'service_type', 'address'])]
class Vendor extends Model
{
    use HasFactory;

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function expenseEntries(): HasMany
    {
        return $this->hasMany(ExpenseEntry::class);
    }
}
