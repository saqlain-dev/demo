<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrientationPlanActivity extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function orientationParticipants(): HasMany
    {
        return $this->hasMany(OrientationParticipant::class,'orientation_plan_activity_id');
    }

    public function ExecutedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'executed_by','id');
    }
}
