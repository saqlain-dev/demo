<?php

namespace App\Models\HR\Recruitment;

use App\Http\Controllers\Api\V1\Communication\CommunicationCommentController;
use App\Models\Communication\CommunicationComment;
use App\Models\Employee;
use App\Models\Questionnaire\Questionnaire;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplyJob extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
    public function JobId():BelongsTo
    {
        return $this->belongsTo(ManageJob::class,'job_id');
    }

    public function InterviewPanel(): HasMany
    {
        return $this->HasMany(InterviewCommittee::class, 'apply_job_id');
    }
    public function OfferLetter(): HasMany
    {
        return $this->HasMany(OfferLetter::class, 'apply_job_id');
    }

    public function ScheduledInterviews(): HasMany
    {
        return $this->hasMany(ScheduleInterview::class,'apply_job_id');
    }

    public function candidateOnlineTest(): HasOne
    {
        return $this->hasOne(CandidateOnlineTest::class, 'apply_job_id');
    }

    public function questionnaires(): MorphMany
    {
        return $this->morphMany(Questionnaire::class, 'questionnaireable');
    }

    public function PanelistAnswers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class,'apply_job_id');
    }

    public function CandidateHistory(): HasMany
    {
        return $this->hasMany(CandidateHistory::class,'apply_job_id');
    }

    public function PoolBucketType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'pool_bucket_type');
    }

    public function comments(): HasMany
    {
        return $this->HasMany(CommunicationComment::class, 'apply_job_id');
    }

    public function interviewResults(): HasMany
    {
        return $this->hasMany(InterviewResult::class, 'apply_job_id');
    }
}
