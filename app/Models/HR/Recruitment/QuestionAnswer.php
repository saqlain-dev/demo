<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionAnswer extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function EmployeeId():BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function ApplyJobId(): BelongsTo
    {
        return $this->belongsTo(ApplyJob::class, 'apply_job_id');
    }
    public function InterviewQuestionId(): BelongsTo
    {
        return $this->belongsTo(InterviewQuestion::class, 'interview_question_id');
    }
    public function QuestionOptionId(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'question_option_id');
    }
    public function InterviewId():BelongsTo
    {
        return  $this->belongsTo(ScheduleInterview::class, 'interview_id');
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
