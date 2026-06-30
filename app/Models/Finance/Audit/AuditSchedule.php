<?php

namespace App\Models\Finance\Audit;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\Finance\Audit\AuditPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditSchedule extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class, 'audit_plan_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }

    public function ticketSchedule(): HasMany
    {
        return $this->hasMany(TicketSchedule::class, 'audit_schedule_id');
    }
    public function auditPlanReport(): HasMany
    {
        return $this->hasMany(AuditPlanReport::class, 'audit_schedule_id');
    }


}
