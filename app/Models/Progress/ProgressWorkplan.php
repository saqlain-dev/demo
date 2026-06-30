<?php

namespace App\Models\Progress;

use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressWorkplan extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    public function workPlanGoals(): HasMany
    {
        return $this->hasMany(ProgressWorkplanGoals::class);
    }

    public function workPlanOutcome(): HasMany
    {
        return $this->hasMany(ProgressWorkplanOutcome::class);
    }

    public function workPlanOutput(): HasMany
    {
        return $this->hasMany(ProgressWorkplanOutput::class);
    }
    public function CreatedBy(): BelongsTo
    {
        return  $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function UpdatedBy(): BelongsTo
    {
        return  $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }
}
