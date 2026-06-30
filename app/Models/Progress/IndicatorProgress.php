<?php

namespace App\Models\Progress;

use App\Models\Questionnaire\Questionnaire;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndicatorProgress extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function IndicatorMovs(): HasMany
    {
        return $this->hasMany(IndicatorProgressMovs::class,'indicator_progress_id');
    }

    public function questionnaires(): MorphMany
    {
        return $this->morphMany(Questionnaire::class, 'questionnaireable');
    }
    public function ProgressWorkplanId(): BelongsTo
    {
        return  $this->belongsTo(ProgressWorkplan::class, 'progress_workplan_id');
    }
    public  function ProgressStatus(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class, 'progress_status','id')->select(['id','name']);
    }

    public function FormId(): BelongsTo
    {
        return  $this->belongsTo(QuestionnaireForm::class, 'form_id','id')->select(['id','name']);
    }
    protected $casts = [
        'form_id' => 'array',
        'reporting_level' => 'array',
    ];
}
