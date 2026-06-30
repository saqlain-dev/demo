<?php

namespace App\Models\Finance\Audit;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\Audit\AuditPlanReport;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditPlan extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->select(['id', 'name']);
    }

    public function audiSchedule(): HasMany
    {
        return $this->hasMany(AuditSchedule::class,'audit_plan_id');
    }
}
