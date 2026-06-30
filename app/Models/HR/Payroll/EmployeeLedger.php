<?php

namespace App\Models\HR\Payroll;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLedger extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
}
