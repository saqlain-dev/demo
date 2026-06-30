<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAllowanceDeduction extends Model
{
    use LogEvents;
    protected $guarded=['id'];
}
