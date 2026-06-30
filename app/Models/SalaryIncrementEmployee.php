<?php

namespace App\Models;

use App\Models\Configuration\EmployeeChangeLog;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryIncrementEmployee extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function changeLogs()
    {
        return $this->hasMany(EmployeeChangeLog::class,'salary_increment_employees_id');
    }

    public function appraisalSalarySetups()
    {
        return $this->hasMany(AppraisalSalarySetup::class,'salary_increment_employees_id');
    }

    public function employeeAllowanceDeduction(): HasMany
    {
        return $this->hasMany(AppraisalEmployeeAllowance::class,'salary_increment_employees_id');
    }
}
