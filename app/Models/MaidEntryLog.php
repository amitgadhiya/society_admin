<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaidEntryLog extends Model
{
    protected $fillable = [
        'society_id', 'maid_id', 'watchman_id', 'enter_time', 'exit_time', 'status',
    ];

    protected $casts = [
        'enter_time' => 'datetime',
        'exit_time'  => 'datetime',
    ];

    public function maid()
    {
        return $this->belongsTo(Maid::class);
    }

    public function watchman()
    {
        return $this->belongsTo(Watchman::class);
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }
}
