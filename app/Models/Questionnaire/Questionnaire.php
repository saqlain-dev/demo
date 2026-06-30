<?php

namespace App\Models\Questionnaire;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Questionnaire extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function questionnaireable(): MorphTo
    {
        return $this->morphTo();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'questionnaire_id');
    }

    public function formId(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireForm::class, 'form_id');
    }
}
