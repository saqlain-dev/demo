<?php

namespace App\Models;

use App\Models\Finance\BankInfo;
use App\Models\HR\Payroll\EmployeePayrollSegregation;
use App\Models\HR\Payroll\EmployeeSalarySegregation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayrollDetail extends Model
{
    use LogEvents;
    protected $guarded=['id'];

    public function allowanceDeduction(): HasMany
    {
        return $this->hasMany(SalaryAllowanceDeduction::class,'PayrollDetailId');
    }

    public function salaryAllownaceDeduction(): HasMany
    {
        return $this->hasMany(SalaryAllowanceDeduction::class,'PayrollDetailId');
    }
    public function salarySegregation(): HasMany
    {
        return $this->hasMany(EmployeePayrollSegregation::class,'PayrollDetailId');
    }

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'EmployeeId');
    }

    public function BankDetail(): BelongsTo
    {
        return $this->belongsTo(BankInfo::class, 'BankID');
    }
    public function BankName(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'BankID');
    }

    public function preGrossSalaryAllowances(): HasMany
    {
        return $this->hasMany(EmployeePayrollPreGrossSalary::class,'PayrollDetailId');
    }
}
