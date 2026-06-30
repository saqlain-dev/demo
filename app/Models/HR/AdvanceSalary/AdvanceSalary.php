<?php

namespace App\Models\HR\AdvanceSalary;

use App\Models\Employee;
use App\Models\Finance\Voucher\Voucher;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvanceSalary extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(AdvanceSalaryInstallment::class, 'advance_salary_id');
    }

    public function loanCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'loan_category_id');
    }

    public function VoucherId(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
