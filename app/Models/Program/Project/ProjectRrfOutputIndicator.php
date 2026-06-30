<?php

namespace App\Models\Program\Project;

use App\Models\Activity;
use App\Models\Program\LasRrfOutputIndicator;
use App\Models\Progress\IndicatorProgress;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\Progress\ProgressWorkplanOutput;
use App\Models\StrategicPlanIndicator;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRrfOutputIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;

    protected $guarded = ['id'];

    public function proWorkPlanIndicators(): HasOne
    {
        return $this->HasOne(ProgressWorkplanOutput::class,'output_indicator_id');
    }

    public function proWorkPlanIndicatorsActivites(): HasMany
    {
        return $this->HasMany(Activity::class,'activityable_id');
    }
    public function KpiMappedIndicators(): HasOne
    {
        return $this->HasOne(ProjectKpiMapping::class,'indicator_number')->where('type_of_indicator','3');
    }

    public function proOutputIndicatorTargets(): HasMany
    {
        return $this->hasMany(ProjectRrfOutputIndicatorTarget::class);
    }

    public function SpIndicatorId(): BelongsTo
    {
        return $this->belongsTo(StrategicPlanIndicator::class, 'sp_indicator_id')->select('id','name');
    }

    public function lasRrfOutputIndicatorId(): BelongsTo
    {
        return $this->belongsTo(LasRrfOutputIndicator::class,'las_rrf_output_indicator_id');
    }

    public function projectRrfOutcomeIndicatorId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutcomeIndicator::class,'project_rrf_outcome_indicator_id');
    }
}
