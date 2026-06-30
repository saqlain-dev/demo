<?php

namespace App\Models\Program\Project;

use App\Models\Program\ResultResourceFrameworkOutput;
use App\Models\StrategicPlan;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRrfOutput extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function ProOutputIndicators(): HasMany
    {
        return $this->hasMany(ProjectRrfOutputIndicator::class, 'proj_rrf_output_id');
    }

    public function project_rrf_goal(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfGoal::class, 'project_rrf_goal_id');
    }

    public function project_rrf_outcome(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutcome::class, 'project_rrf_outcome_id');
    }
    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }

    public function lasRrfOutputId(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFrameworkOutput::class,'las_rrf_output_id');
    }
}
