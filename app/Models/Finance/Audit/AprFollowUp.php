<?php

namespace App\Models\Finance\Audit;

use App\Models\Comment;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\Finance\Audit\AuditPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AprFollowUp extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function auditPlanReport(): BelongsTo
    {
        return $this->belongsTo(AuditPlanReport::class, 'audit_plan_report_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function followUpStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'apr_follow_up_status_id')->select(['id', 'name']);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'priority')->select(['id', 'name']);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'responsible_person');
    }
}
