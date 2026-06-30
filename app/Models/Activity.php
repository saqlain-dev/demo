<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
class Activity extends Model
{
    use LogEvents, SoftDeletes;
        protected static function boot()
    {
        parent::boot();

        static::updating(function ($activity) {
            $activity->updated_by = Auth::id();
        });

        static::creating(function ($activity) {
            $activity->created_by = Auth::id(); 
        });
    }
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
    
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }
}
