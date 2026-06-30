<?php

namespace App\Models\Program\Project;

use App\Models\Comment;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Progress\ProgressWorkplanOutput;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\District;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use App\Models\StrategicPlan;
use App\Models\Program\ProjectDonor;
use App\Models\Donar\DonarProfile;
use App\Models\Finance\Currency;
use Illuminate\Database\Eloquent\Model;
use App\Models\Progress\ProgressWorkplan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\ProjectImplementingPartner;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Program\Project\MnE\ProjectMneWorkplan;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectProfile extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    // protected $with = ['created_by','updated_by'];

    public function projectGoals(): HasMany
    {
        return $this->hasMany(ProjectRrfGoal::class,'project_id');
    }
    public function projectOutputs(): HasMany
    {
        return $this->hasMany(ProjectRrfOutput::class,'project_id');
    }
    public function lasSpDetail(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class,'las_sp_statement','id')->select(['id','name']);
    }
    public function mnePlans(): HasMany
    {
        return $this->hasMany(ProjectMnePlan::class,'project_id');
    }
    public function activityCalendars(): HasMany
    {
        return $this->hasMany(ActivityCalendar::class,'project_id');
    }
    public function progressWorkplans(): HasMany
    {
        return $this->hasMany(ProgressWorkplan::class,'project_id');
    }

    public function ProgressWorkPlanOutputByProjects(): HasMany
    {
        return  $this->hasMany(ProgressWorkplanOutput::class,'project_id');
    }
    public function mneWorkplans(): HasMany
    {
        return $this->hasMany(ProjectMneWorkplan::class,'project_id');
    }
    public function thematic_area(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'thematic_area')->select(['id','name']);
    }

    public function getThematicArea(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'thematic_area')->select(['id','name']);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'status')->select(['id','name']);
    }
    public function pdu_focal_person(): BelongsTo
    {
        return $this->belongsTo(User::class,'pdu_focal_person_id')->select(['id','name']);
    }
    public function project_manager(): BelongsTo
    {
        return $this->belongsTo(User::class,'project_manager_id')->select(['id','name']);
    }
    public function implementing_partner(): BelongsToMany
    {
        return $this->belongsToMany(ProjectImplementingPartner::class, 'implementing_partner_project', 'project_id', 'partner_id');
    }
    public function provinces(): BelongsToMany
    {
        return $this->belongsToMany(Province::class, 'project_profile_province', 'project_profile_id', 'province_id');
    }
    public function donor_sync(): BelongsToMany
    {
        return $this->belongsToMany(ProjectDonor::class, 'project_donors', 'project_id', 'donor_id');
    } 
    public function donors(): BelongsToMany
    {
        return $this->belongsToMany(DonarProfile::class, 'project_donors', 'project_id', 'donor_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public static function approvedProjects(): Collection
    {
       return  self::query()->where('approval_status',1)->with('donor.donorDetail')->get();
    }
    public function donor(): HasMany
    {
        return $this->hasMany(ProjectDonor::class, 'project_id');
    }

    public function getDistrictsAttribute()
    {
        if ($this->target_area) {
            $targetAreaIds = array_filter(explode(',', $this->target_area), function($value) {
                return is_numeric($value);
            });
            return District::whereIn('id', $targetAreaIds)->select('id','name')->get();
        }

        return collect();
    }

    public function projectBudget(): HasOne
    {
        return $this->hasOne(ProjectBudget::class,'project_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

}
