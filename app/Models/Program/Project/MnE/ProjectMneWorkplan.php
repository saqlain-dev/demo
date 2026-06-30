<?php

namespace App\Models\Program\Project\MnE;

use App\Models\Activity;
use App\Models\District;
use App\Models\Employee;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Questionnaire\Questionnaire;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMneWorkplan extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class)->select(['id', 'name']);
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function focalPerson(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'project_focal_person');
    }
    public function responsiblePerson(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'mne_responsible_person');
    }

    public function questionnaires(): MorphMany
    {
        return $this->morphMany(Questionnaire::class, 'questionnaireable');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class)->select(['id','name']);
    }

    public function FormId(): BelongsTo
    {
        return  $this->belongsTo(QuestionnaireForm::class, 'form_id','id')->select(['id','name']);
    }

    public function mneKpi(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'mne_kpis');
    }

}
