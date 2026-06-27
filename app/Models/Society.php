<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'code', 'address', 'city', 'state', 'pincode', 'billing_day'])]
class Society extends Model
{
    use HasFactory;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function wings(): HasMany
    {
        return $this->hasMany(Wing::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function unitMembers(): HasMany
    {
        return $this->hasMany(UnitMember::class);
    }

    public function billingCycles(): HasMany
    {
        return $this->hasMany(BillingCycle::class);
    }

    public function maintenanceBills(): HasMany
    {
        return $this->hasMany(MaintenanceBill::class);
    }

    public function paymentReceipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function incomeCategories(): HasMany
    {
        return $this->hasMany(IncomeCategory::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }

    public function expenseEntries(): HasMany
    {
        return $this->hasMany(ExpenseEntry::class);
    }

    public function maintenancePlans(): HasMany
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function unitTypes(): HasMany
    {
        return $this->hasMany(UnitType::class);
    }
}
