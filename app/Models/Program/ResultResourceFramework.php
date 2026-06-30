<?php

namespace App\Models\Program;

use App\Models\Program\ResultResourceFrameworkOutcome;
use App\Models\Program\ResultResourceFrameworkOutput;
use App\Models\StrategicPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultResourceFramework extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function goalIndicators(): HasMany
    {
        return $this->hasMany(LasRrfGoalIndicator::class, 'rrf_goal_id');
    }
    public function rrf_outcomes(): HasMany
    {
        return $this->hasMany(ResultResourceFrameworkOutcome::class, 'goal_id');
    }
    public function rrf_outputs(): HasMany
    {
        return $this->hasMany(ResultResourceFrameworkOutput::class,'goal_id');
    }

    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }

    public function sPDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);

    }

}
