<?php

namespace App\Models\Finance\Budget;

use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectBudgetLog extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function BudgetDetail():HasMany
    {
        return $this->HasMany(ProjectBudgetDetailLog::class,'project_budget_log_id');
    }

    public function ProjectId():BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function approvalProcessLogs(): HasMany
    {
        return $this->hasMany(ProjectBudgetApprovalLog::class, 'project_budget_log_id');
    }

}
