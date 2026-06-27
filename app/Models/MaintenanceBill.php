<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'society_id',
    'billing_cycle_id',
    'unit_id',
    'bill_no',
    'bill_date',
    'due_date',
    'opening_balance',
    'total_charges',
    'total_discount',
    'late_fee',
    'total_paid',
    'closing_balance',
    'status',
])]
class MaintenanceBill extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bill_date' => 'date',
            'due_date' => 'date',
            'opening_balance' => 'decimal:2',
            'total_charges' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'late_fee' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(BillingCycle::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceBillItem::class);
    }

    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
