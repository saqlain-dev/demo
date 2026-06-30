<?php

namespace App\Models\Program\Project;

use App\Models\Program\LasRrfGoalIndicator;
use App\Models\Program\ResultResourceFramework;
use App\Models\StrategicPlan;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRrfGoal extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function projectOutcomes(): HasMany
    {
        return $this->hasMany(ProjectRrfOutcome::class);
    }

    public function ProGoalIndicators(): HasMany
    {
        return $this->hasMany(ProjectRrfGoalIndicator::class, 'proj_rrf_goal_id');
    }
    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }

    public function lasRrfGoalId(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFramework::class,'las_rrf_goal_id');
    }
}
