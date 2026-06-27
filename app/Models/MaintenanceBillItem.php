<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['maintenance_bill_id', 'charge_name', 'charge_code', 'amount', 'sort_order'])]
class MaintenanceBillItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function maintenanceBill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class);
    }
}
