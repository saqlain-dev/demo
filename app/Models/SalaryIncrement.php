<?php

namespace App\Models;

use App\Models\Configuration\EmployeeChangeLog;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryIncrement extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function financialYear()
    {
        return $this->belongsTo(TypeValue::class, 'financial_year');
    }

    public function salaryIncrementEmployees()
    {
        return $this->hasMany(SalaryIncrementEmployee::class);
    }

    public function employeeChangeLogs()
    {
        return $this->hasMany(EmployeeChangeLog::class, 'salary_increments_id');
    }

    public function appraisalSalarySetups()
    {
        return $this->hasManyThrough(AppraisalSalarySetup::class, SalaryIncrementEmployee::class, 'salary_increment_id', 'salary_increment_employees_id');
    }

    public function empAllowances()
    {
        return $this->hasManyThrough(AppraisalEmployeeAllowance::class, SalaryIncrementEmployee::class, 'salary_increment_id', 'salary_increment_employees_id');
    }
}
