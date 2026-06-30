<?php

namespace App\Models\Finance\Budget;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualBudgetDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function AnnualBudgetId():BelongsTo
    {
        return $this->belongsTo(AnnualBudget::class,'annual_budget_id');
    }

    public function HeadId():BelongsTo
    {
        return  $this->belongsTo(ChartOfAccount::class,'head_id');
    }
    public function ProjectId():BelongsTo
    {
        return  $this->belongsTo(ProjectProfile::class,'project_id');
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
