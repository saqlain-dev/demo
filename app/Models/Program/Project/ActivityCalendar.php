<?php

namespace App\Models\Program\Project;

use App\Models\Activity;
use App\Models\District;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityCalendar extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $with = ['created_by','updated_by','district','focal_person','activity'];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function focal_person(): BelongsTo
    {
        return $this->belongsTo(User::class,'focal_person_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class)->select(['id','name']);
    }
}
