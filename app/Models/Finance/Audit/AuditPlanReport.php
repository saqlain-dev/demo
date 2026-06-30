<?php

namespace App\Models\Finance\Audit;

use App\Models\User;
use App\Models\Finance\Audit\AuditPlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
}
