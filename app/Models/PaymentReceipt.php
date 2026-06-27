<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'society_id',
    'receipt_no',
    'user_id',
    'unit_id',
    'payment_date',
    'amount',
    'opening_balance_applied',
    'payment_mode',
    'reference_no',
    'notes',
    'status',
])]
class PaymentReceipt extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'opening_balance_applied' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }
}
