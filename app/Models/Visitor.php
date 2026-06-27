<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'society_id',
    'visitor_name',
    'photo',
    'mobile',
    'visitor_code',
    'otp',
    'otp_expires_at',
    'in_at',
    'out_at',
    'visit_to_unit_id',
    'watchman_id',
    'unit_id',
    'reason',
    'status',
    'remarks',
    'rejection_reason',
    'vehicle_number',
    'id_proof',
    'created_by',
])]
class Visitor extends Model
{
    use HasFactory;

    protected $casts = [
        'in_at' => 'datetime',
        'out_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    /**
     * Get the society this visitor belongs to
     */
    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Get the unit being visited
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'visit_to_unit_id');
    }

    /**
     * Get the watchman who added this visitor record (if applicable)
     */
    public function watchman(): BelongsTo
    {
        return $this->belongsTo(Watchman::class);
    }

    /**
     * Get the unit resident who added this visitor record (if applicable)
     */
    public function addedByUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the user who created this record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
