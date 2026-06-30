<?php

namespace App\Models\Program\Project;

use App\Models\Program\ResultResourceFrameworkOutcome;
use App\Models\StrategicPlan;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRrfOutcome extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function projectOutputs(): HasMany
    {
        return $this->hasMany(ProjectRrfOutput::class);
    }

    public function ProOutcomeIndicators(): HasMany
    {
        return $this->hasMany(ProjectRrfOutcomeIndicator::class, 'proj_rrf_outcome_id');
    }

    public function project_rrf_goal(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoal::class,'project_rrf_goal_id');
    }
    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }

    public function lasRrfOutcomeId(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFrameworkOutcome::class,'las_rrf_outcome_id')->select('id','rrf_outcome_number','rrf_outcome_statement');
    }
}
