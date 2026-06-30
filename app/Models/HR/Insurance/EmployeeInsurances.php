<?php

namespace App\Models\HR\Insurance;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeInsurances extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function relatives(): HasMany
    {
        return $this->hasMany(EmployeeRelative::class, 'employee_insurance_id');
    }
    
    public function claimReimbursements(): HasMany
    {
        return $this->hasMany(EmployeeClaimReimbursement::class, 'employee_insurance_id');
    }

}
