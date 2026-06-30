<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppraisalEmployeeAllowance extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function employeeDetail()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
}
