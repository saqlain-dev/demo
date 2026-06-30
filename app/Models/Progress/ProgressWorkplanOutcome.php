<?php

namespace App\Models\Progress;

use App\Models\Activity;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressWorkplanOutcome extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class,'activityable');
    }

    public function OutcomeId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutcome::class, 'outcome_id');
   }
    public function OutcomeStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'outcome_status','id')->select('id','name');
    }

    public function OutcomeIndicatorId(): BelongsTo
    {
        return  $this->belongsTo(ProjectRrfOutcomeIndicator::class, 'outcome_indicator_id');
   }
        public function getOutcomemovesAttribute()
        {
            // Explode the comma-separated ids
            $movIds = explode(',', $this->outcome_movs_ids);

            // Retrieve the corresponding Type_values
            return TypeValue::whereIn('id', $movIds)->select('id','name')->get();
        }
}
