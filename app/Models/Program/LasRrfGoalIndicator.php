<?php

namespace App\Models\Program;

use App\Models\StrategicPlanIndicator;
use App\Models\StrategicPlanIndicatorTarget;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasRrfGoalIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function lasGoal(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFramework::class,'rrf_goal_id');
    }

    public function goalIndicatorTargets(): HasMany
    {
        return $this->hasMany(LasRrfGoalIndicatorTarget::class);
    }

    public function SpIndicatorId(): BelongsTo
    {
        return $this->belongsTo(StrategicPlanIndicator::class, 'sp_indicator_id')->select('id','name');
    }
}
