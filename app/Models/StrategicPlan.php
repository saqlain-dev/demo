<?php

namespace App\Models;

use App\Models\Program\ResultResourceFramework;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrategicPlan extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
    public function pillars(): HasMany
    {
        return $this->hasMany(StrategicPlanPillar::class);
    }

    public function user(): BelongsTo
    {
        return  $this->belongsTo(User::class,'created_by');
    }

    public function indicators(): HasManyThrough
    {
        return $this->hasManyThrough(StrategicPlanIndicator::class, StrategicPlanPillar::class);
    }

    public function resultResourceFrameworks(): HasMany
    {
        return  $this->hasMany(ResultResourceFramework::class,'las_sp_statement');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
