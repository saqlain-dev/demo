<?php

namespace App\Models\Program\Project\MnE;

use App\Models\Comment;
use App\Models\Questionnaire\Questionnaire;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMnePlan extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $with = ['created_by','updated_by'];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function planGoals(): HasMany
    {
        return $this->hasMany(MnePlanGoal::class,'plan_id');
    }
    public function planOutputs(): HasMany
    {
        return $this->hasMany(MnePlanOutput::class,'plan_id');
    }
    public function planOutcomes(): HasMany
    {
        return $this->hasMany(MnePlanOutcome::class,'plan_id');
    }


    public function mnePlanDetails(): HasMany
    {
        return $this->hasMany(MnePlanDetail::class,'plan_id');
    }
    public function questionnaires(): MorphMany
    {
        return $this->morphMany(Questionnaire::class, 'questionnaireable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}
