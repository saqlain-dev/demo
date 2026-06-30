<?php

namespace App\Models\Finance\Audit;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\Audit\AuditPlanReport;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditPlan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function auditPlanReport(): HasOne
    {
        return $this->hasOne(AuditPlanReport::class, 'audit_plan_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->select(['id', 'name']);
    }
}
