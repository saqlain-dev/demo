<?php

namespace App\Models\Progress;

use App\Models\Activity;
use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressWorkplanGoals extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class,'activityable');
    }

    public function GoalId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoal::class, 'goal_id');
    }
    public function GoalStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'goal_status','id')->select('id','name');
    }
    public function GoalIndicatorId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoalIndicator::class,'goal_indicator_id');
    }

    public function getMovesAttribute()
    {
        // Explode the comma-separated ids
        $movIds = explode(',', $this->goal_movs_ids);

        // Retrieve the corresponding Type_values
        return TypeValue::whereIn('id', $movIds)->select('id','name')->get();
    }

}
