<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['society_id', 'name', 'code'])]
class IncomeCategory extends Model
{
    use HasFactory;

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }
}
