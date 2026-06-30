<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRequisition extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    protected $casts = [
        'required_skills' => 'array',
    ];

    public function RequesterId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'requester_id');
    }
    public function HiringSupervisorId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'hiring_supervisor_id');
    }
    public function replacementForMs(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'replacement_for_ms');
    }
    public function DepartmentId(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department_id','id')->select(['id','name']);
    }
    public function RequiredContractType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'required_contract_type','id')->select(['id','name']);
    }
    public function RequiredJobType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'required_job_type','id')->select(['id','name']);
    }

    public function JobMode(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'job_mode','id')->select(['id','name']);
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function Jobs(): HasMany
    {
        return $this->hasMany(ManageJob::class, 'requisition_id');
    }

    public function RecruitmentPlan(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPlan::class,'recruitment_plan_id','id');
    }

     public function RecruitmentPlanDetails(): BelongsTo
    {
        return $this->belongsTo(RecruitmentPlanDetail::class,'recruitment_plan_detail_id','id');
    }
}
