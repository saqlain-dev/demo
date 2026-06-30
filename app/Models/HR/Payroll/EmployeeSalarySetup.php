<?php

namespace App\Models\HR\Payroll;

use App\Models\Employee;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalarySetup extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function bankDetail(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'bankId');
    }

    public function salarySegregation(): HasMany
    {
        return $this->hasMany(EmployeeSalarySegregation::class,'emp_salary_setup_id');
    }

    public function coa(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'coa_id');
    }
}
