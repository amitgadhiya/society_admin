<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_receipt_id', 'maintenance_bill_id', 'allocated_amount'])]
class PaymentAllocation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
        ];
    }

    public function paymentReceipt(): BelongsTo
    {
        return $this->belongsTo(PaymentReceipt::class);
    }

    public function maintenanceBill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class);
    }
}
