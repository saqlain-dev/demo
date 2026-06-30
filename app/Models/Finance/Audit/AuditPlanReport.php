<?php

namespace App\Models\Finance\Audit;

use App\Models\Comment;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\Finance\Audit\AuditPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AuditPlanReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function auditPlan(): BelongsTo
    {
        return $this->belongsTo(AuditPlan::class, 'audit_plan_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->select(['id', 'name']);
    }

    public function followUp(): HasMany
    {
        return $this->hasMany(AprFollowUp::class, 'audit_plan_report_id');
    }

    public function auditPlanStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'status');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
