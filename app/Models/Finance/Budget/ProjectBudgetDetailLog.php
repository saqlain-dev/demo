<?php

namespace App\Models\Finance\Budget;

use App\Models\Activity;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectBudgetDetailLog extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function ProjectBudgetId():BelongsTo
    {
        return $this->belongsTo(ProjectBudget::class,'project_budget_log_id');
    }


    public function Head():BelongsTo
    {
        return  $this->belongsTo(ChartOfAccount::class,'head_id');
    }
    public function head_id():BelongsTo
    {
        return  $this->belongsTo(ChartOfAccount::class,'head_id');
    }
    public function SubCategoryId():BelongsTo
    {
        return  $this->belongsTo(BudgetCategory::class,'sub_category_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
