<?php

namespace App\Models\Program;
use App\Models\StrategicPlan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultResourceFrameworkOutcome extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function outcomeIndicators(): HasMany
    {
        return $this->hasMany(LasRrfOutcomeIndicator::class, 'rrf_outcome_id');
    }
    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }
    public function sPDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'sp_statement','id')->select(['id','name']);

    }

    public function lasGoal(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFramework::class,'goal_id');
    }
}
