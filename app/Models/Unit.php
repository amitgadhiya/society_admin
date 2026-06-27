<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'society_id',
    'wing_id',
    'unit_type_id',
    'unit_number',
    'floor',
    'unit_type',
    'area_sqft',
    'status',
    'opening_balance',
    'registered_in_name_of',
    'contact_number',
])]
class Unit extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function wing(): BelongsTo
    {
        return $this->belongsTo(Wing::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(UnitMember::class);
    }

    public function maintenanceBills(): HasMany
    {
        return $this->hasMany(MaintenanceBill::class);
    }

    public function paymentReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }
}
