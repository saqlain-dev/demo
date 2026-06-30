<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'contract_type_id');
    }
    
    public function parentEmployeeContract(): BelongsTo
    {
        return $this->belongsTo(ParentEmployeeContract::class, 'parent_employee_contract_id');
    }
}
