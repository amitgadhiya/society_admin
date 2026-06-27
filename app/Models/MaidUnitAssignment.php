<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaidUnitAssignment extends Model
{
    protected $fillable = [
        'maid_id', 'unit_id', 'type', 'start_date', 'end_date', 'start_time', 'end_time', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function maid()
    {
        return $this->belongsTo(Maid::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
