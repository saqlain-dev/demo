<?php

namespace App\Models\HR\Recruitment;

use App\Models\Finance\Budget\ProjectBudget;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentPlan extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function BudgetId(): BelongsTo
    {
        return $this->belongsTo(ProjectBudget::class, 'budget_id');
    }

    public function RecruitmentPlanDetail(): HasMany
    {
        return $this->hasMany(RecruitmentPlanDetail::class, 'recruitment_plan_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
