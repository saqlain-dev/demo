<?php

namespace App\Models\HR\Recruitment;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewQuestion extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function QOptions(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'interview_question_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
