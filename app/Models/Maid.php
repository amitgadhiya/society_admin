<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maid extends Model
{
    protected $fillable = [
        'society_id', 'name', 'mobile', 'photo', 'aadhaar_number', 'address', 'status',
    ];

    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    public function unitAssignments()
    {
        return $this->hasMany(MaidUnitAssignment::class);
    }
}
