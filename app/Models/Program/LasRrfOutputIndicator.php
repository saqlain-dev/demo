<?php

namespace App\Models\Program;

use App\Models\StrategicPlanIndicator;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasRrfOutputIndicator extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function lasOutput(): BelongsTo
    {
        return $this->belongsTo(ResultResourceFrameworkOutput::class, 'rrf_output_id');
    }

    public function outputIndicatorsTarget(): HasMany
    {
        return $this->hasMany(LasRrfOutputIndicatorTarget::class);
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
