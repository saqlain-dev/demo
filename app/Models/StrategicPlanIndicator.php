<?php

namespace App\Models;

use App\Models\Program\LasRrfGoalIndicator;
use App\Models\Program\LasRrfOutcomeIndicator;
use App\Models\Program\LasRrfOutputIndicator;
use App\Models\Program\ResultResourceFramework;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrategicPlanIndicator extends Model
{
    protected $guarded = ['id'];
    use SoftDeletes, HasFactory, LogEvents;
    public function indicatorTargets(): HasMany
    {
        return $this->hasMany(StrategicPlanIndicatorTarget::class);
    }
    public function indicatorYears(): HasMany
    {
        return $this->hasMany(StrategicPlanIndicatorYear::class);
    }

    public function lasGoalIndicators(): HasMany
    {
        return $this->hasMany(LasRrfGoalIndicator::class, 'sp_indicator_id');
    }
    public function lasOutputIndicators(): HasMany
    {
        return $this->hasMany(LasRrfOutputIndicator::class, 'sp_indicator_id');
    }
    public function lasOutcomeIndicators(): HasMany
    {
        return $this->hasMany(LasRrfOutcomeIndicator::class, 'sp_indicator_id');
    }

}
