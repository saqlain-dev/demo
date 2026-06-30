<?php

namespace App\Models\Progress;

use App\Models\Activity;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressWorkplanOutput extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class,'activityable');
    }

    public function proWorkplanIndicatorProgress(): HasOne
    {
        return $this->hasOne(IndicatorProgress::class,'indicator_id','output_indicator_id')->where('type_of_indicator', 3);
    }

    public function OutputId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutput::class,'output_id');
    }

    public function OutputStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'output_status','id')->select('id','name');
    }

    public function OutputIndicatorId(): BelongsTo
    {
        return $this->belongsTo(ProjectRrfOutputIndicator::class,'output_indicator_id');
    }
    public function getOutputmovesAttribute()
    {
        // Explode the comma-separated ids
        $movIds = explode(',', $this->output_movs_ids);

        // Retrieve the corresponding Type_values
        return TypeValue::whereIn('id', $movIds)->select('id','name')->get();
    }
}
