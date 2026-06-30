<?php

namespace App\Models\Program\Rdu;

use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Progress\ProgressWorkplan;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchMatrix extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    public function DataSources(): HasMany
    {
        return $this->hasMany(ResearchMatrixDataSources::class,'research_matrix_id');
    }
    public function ReserachOutputs(): HasMany
    {
        return $this->hasMany(ResearchMatrixResearchOutput::class,'research_matrix_id');
    }
    public function RmResources(): HasMany
    {
        return $this->hasMany(ResearchMatrixResources::class,'research_matrix_id');
    }
    public function ProgramName(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'program_name')->select(['id','project_name','created_by','updated_by']);
    }

    public function CreatedBy(): BelongsTo
    {
        return  $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function UpdatedBy(): BelongsTo
    {
        return  $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function FocalPerson(): BelongsTo
    {
        return  $this->belongsTo(User::class,'focal_person')->select(['id','name']);
    }

    public function Responsible(): BelongsTo
    {
        return  $this->belongsTo(User::class,'responsible')->select(['id','name']);
    }

    public function Accountable(): BelongsTo
    {
        return  $this->belongsTo(User::class,'accountable')->select(['id','name']);
    }
    public function Consulted(): BelongsTo
    {
        return  $this->belongsTo(User::class,'consulted')->select(['id','name']);
    }
    public function Informed(): BelongsTo
    {
        return  $this->belongsTo(User::class,'informed')->select(['id','name']);
    }
    public function ProgressWorkplanId(): BelongsTo
    {
        return  $this->belongsTo(ProgressWorkplan::class,'progress_workplan_id')->select(['id','project_workplan']);
    }

    public function MethodologyId(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'methodology_id')->select(['id','name']);
    }
    public function ResearchComponentPlaceId(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'research_component_place_id')->select(['id','name']);
    }
}
