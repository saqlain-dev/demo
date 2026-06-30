<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function shiftDetail(): HasMany
    {
        return $this->hasMany(ShiftDetail::class,'shift_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class,'shift_id');
    }
}
