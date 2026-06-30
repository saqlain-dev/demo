<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    protected $with = ['category'];

    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'activity_cat');
    }
    public function checkinsheetactivities(): HasOne
    {
        return $this->hasOne(CheckInSheet::class,'activitie_id');
    }
}
