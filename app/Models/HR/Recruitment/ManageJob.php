<?php

namespace App\Models\HR\Recruitment;

use App\Models\Communication\CommunicationComment;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManageJob extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function DepartmentId(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department_id','id')->select(['id','name']);
    }
    public function RequiredJobType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'required_job_type','id')->select(['id','name']);
    }
    public function RequisitionId(): BelongsTo
    {
        return $this->belongsTo(EmployeeRequisition::class,'requisition_id','id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function comments(): HasMany
    {
        return $this->HasMany(CommunicationComment::class, 'manage_job_id');
    }
}
