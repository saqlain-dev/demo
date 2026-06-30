<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'LeaveTypeID',
        'Description',
        'WithoutBalance',
        'SandwichRule',
    ];
    protected $table = "LeaveTypes";
}
