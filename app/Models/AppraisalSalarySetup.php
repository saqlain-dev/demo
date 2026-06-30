<?php

namespace App\Models;

use App\Models\Finance\BankInfo;
use App\Models\HR\Payroll\EmployeeSalarySegregation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppraisalSalarySetup extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function employeeDetail()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function bankDetail()
    {
        return $this->belongsTo(BankInfo::class, 'bank_id');
    }

    public function salaryIncrementEmployee()
    {
        return $this->belongsTo(SalaryIncrementEmployee::class, 'salary_increment_employees_id');
    }

    public function employeeSalarySegregations()
    {
        return $this->hasMany(EmployeeSalarySegregation::class, 'appraisal_salary_setup_id');
    }
}
