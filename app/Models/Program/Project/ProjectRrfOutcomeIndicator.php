<?php

namespace App\Models\Program\Project;

use App\Models\Activity;
use App\Models\Program\LasRrfOutcomeIndicator;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\Progress\ProgressWorkplanOutcome;
use App\Models\StrategicPlanIndicator;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRrfOutcomeIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
    public function proWorkPlanIndicators(): HasOne
    {
        return $this->HasOne(ProgressWorkplanOutcome::class,'outcome_indicator_id');
    }
    public function proWorkPlanIndicatorsActivites(): HasMany
    {
        return $this->HasMany(Activity::class,'activityable_id');
    }
    public function KpiMappedIndicators(): HasOne
    {
        return $this->HasOne(ProjectKpiMapping::class,'indicator_number')->where('type_of_indicator','2');
    }

    public function proOutcomeIndicatorTargets(): HasMany
    {
        return $this->hasMany(ProjectRrfOutcomeIndicatorTarget::class);
    }

    public function SpIndicatorId(): BelongsTo
    {
        return $this->belongsTo(StrategicPlanIndicator::class, 'sp_indicator_id')->select('id','name');
    }

    public function lasRrfOutcomeIndicatorId(): BelongsTo
    {
        return $this->belongsTo(LasRrfOutcomeIndicator::class,'las_rrf_outcome_indicator_id');
    }

    public function projectRrfGoalIndicatorId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoalIndicator::class,'project_rrf_goal_indicator_id');
    }
}
