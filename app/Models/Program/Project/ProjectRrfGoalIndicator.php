<?php

namespace App\Models\Program\Project;

use App\Models\Activity;
use App\Models\Program\LasRrfGoalIndicator;
use App\Models\Progress\ProgressWorkplan;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\StrategicPlanIndicator;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRrfGoalIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
    public function proWorkPlanIndicators(): HasOne
    {
        return $this->HasOne(ProgressWorkplanGoals::class,'goal_indicator_id');
    }

    public function KpiMappedIndicators(): HasOne
    {
        return $this->HasOne(ProjectKpiMapping::class,'indicator_number')->where('type_of_indicator','1');
    }

    public function proGoalIndicatorTargets(): HasMany
    {
        return $this->hasMany(ProjectRrfGoalIndicatorTarget::class);
    }

    public function SpIndicatorId(): BelongsTo
    {
        return $this->belongsTo(StrategicPlanIndicator::class, 'sp_indicator_id')->select('id','name');
    }

    public function lasRrfGoalIndicatorId(): BelongsTo
    {
        return $this->belongsTo(LasRrfGoalIndicator::class,'las_rrf_goal_indicator_id')->select('id','goal_indicator_number','goal_indicator_statement');
    }

}
