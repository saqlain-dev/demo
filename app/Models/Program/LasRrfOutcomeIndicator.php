<?php

namespace App\Models\Program;

use App\Traits\LogEvents;
use App\Models\StrategicPlanIndicator;
use Illuminate\Database\Eloquent\Model;
use App\Models\Program\LasRrfGoalIndicator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LasRrfOutcomeIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function lasOutcome(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFrameworkOutcome::class, 'rrf_outcome_id');
    }

    public function outcomeIndicatorsTarget(): HasMany
    {
        return $this->hasMany(LasRrfOutcomeIndicatorTarget::class);
    }

    public function SpIndicatorId(): BelongsTo
    {
        return $this->belongsTo(StrategicPlanIndicator::class, 'sp_indicator_id')->select('id','name');
    }

    public function lasGoalIndicator(): BelongsTo
    {
        return $this->belongsTo(LasRrfGoalIndicator::class,'las_goal_indicator_id');
    }
}
