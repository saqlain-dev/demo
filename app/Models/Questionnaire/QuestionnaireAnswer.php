<?php

namespace App\Models\Questionnaire;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionnaireAnswer extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

}
