<?php

namespace App\Models;

use App\Models\HR\Appraisal\AppriasalKpi;
use App\Models\HR\Appraisal\SectionQuestion;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWorkplanActivity extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function employeeWorkplanParticipant(): HasMany
    {
        return $this->hasMany(EmployeeWorkplanParticipant::class,'employee_workplan_activity_id');
    }

    public function KpiId(): BelongsTo
    {
        return $this->belongsTo(AppriasalKpi::class,'kpi_id');
    }

    public function sectionQuestions(): BelongsToMany
    {
        return $this->belongsToMany(SectionQuestion::class, 'employee_workplan_section_question', 'employee_workplan_activity_id', 'section_question_id');
    }
}
