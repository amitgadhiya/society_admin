<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'society_id',
    'unit_id',
    'user_id',
    'member_type',
    'is_primary',
    'start_date',
    'end_date',
])]
class UnitMember extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
