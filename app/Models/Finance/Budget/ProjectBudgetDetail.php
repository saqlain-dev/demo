<?php

namespace App\Models\Finance\Budget;

use App\Models\User;
use App\Models\Activity;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use App\Models\Admin\ProcurementDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectBudgetDetail extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function ProjectBudgetId():BelongsTo
    {
        return $this->belongsTo(ProjectBudget::class,'project_budget_id');
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


    public function UnitType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'unit_type')->select(['id','name']);
    }
    public function budgetCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'budget_category');

    }
    function procurementDetail() {
        return $this->hasMany(ProcurementDetail::class,'budget_details_id');
    }
}
