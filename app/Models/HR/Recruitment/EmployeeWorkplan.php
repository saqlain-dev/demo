<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\EmployeeWorkplanActivity;
use App\Models\HR\Appraisal\SectionQuestion;
use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkplan extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function ProjectId(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function employeeWorkplanActivity(): HasMany
    {
        return $this->hasMany(EmployeeWorkplanActivity::class,'employee_workplan_id');
    }

    public function sectionQuestions(): BelongsToMany
    {
        return $this->belongsToMany(SectionQuestion::class, 'employee_workplan_section_question')
            ->withPivot('employee_workplan_activity_id')
            ->withTimestamps();
    }
}
