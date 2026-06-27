<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'society_id',
        'unit_id',
        'user_id',
        'created_by',
        'updated_by',
        'closed_by',
        'closed_on',
        'title',
        'description',
        'status',
        'before_image',
        'after_image',
        'remark_after_solution',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'closed_on' => 'datetime',
            'rating'    => 'integer',
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

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
