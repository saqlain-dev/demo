<?php

namespace App\Models\Finance\Budget;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualBudget extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function BudgetDetail():HasMany
    {
        return $this->HasMany(AnnualBudgetDetail::class,'annual_budget_id');
    }

    public function BudgetType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'budget_type')->select('id','name');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function projectBudgets(): BelongsToMany
    {
        return $this->belongsToMany(ProjectBudget::class, 'annual_budget_project_budget')
            ->withTimestamps();
    }

}
