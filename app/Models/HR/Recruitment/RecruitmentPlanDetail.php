<?php

namespace App\Models\HR\Recruitment;

use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitmentPlanDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function BudgetDetailId(): BelongsTo
    {
        return $this->belongsTo(ProjectBudgetDetail::class,'budget_detail_id');
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
