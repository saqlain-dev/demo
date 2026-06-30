<?php

namespace App\Models\Program\Project;

use App\Models\Program\LasRrfGoalIndicator;
use App\Models\StrategicPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectKpiMapping extends Model
{
    protected $guarded = ['id'];
    use SoftDeletes, HasFactory, LogEvents;

    public function GoalIndicatorDetail(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoalIndicator::class,'indicator_number','id')->select(['id','goal_indicator_statement']);
    }
    public function OutcomeIndicatorDetail(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutcomeIndicator::class,'indicator_number','id')->select(['id','outcome_indicator_statement']);
    }

    public function OutputIndicatorDetail(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutputIndicator::class,'indicator_number','id')->select(['id','output_indicator_statement']);
    }
}
