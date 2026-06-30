<?php

namespace App\Models\HR\Payroll;

use App\Models\Configuration\AllowanceDeduction;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAllowanceDeduction extends Model
{
    use HasFactory,SoftDeletes,LogEvents;
    protected $guarded=['id'];

    public function allowanceDeductionDetail(): HasOne
    {
        return $this->hasOne(AllowanceDeduction::class, 'id', 'allowance_deduction_id');
    }
}
