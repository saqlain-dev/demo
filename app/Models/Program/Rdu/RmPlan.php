<?php

namespace App\Models\Program\Rdu;

use App\Enum\RmMethodology;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Casts\AsEnumArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RmPlan extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function dataSources(): HasMany
    {
        return $this->hasMany(RmPlanDataSource::class, 'rm_plan_id');
    }

    public function researchPlace(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'research_place_id')->select('id','name');
    }
    public function methodology(): RmMethodology
    {
        return RmMethodology::tryFrom($this->methodology_id);
    }
    public function researchMatrix(): BelongsTo
    {
        return $this->belongsTo(ResearchMatrix::class, 'rm_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by')->select(['id','name']);
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function methodologyDetail(): HasMany
    {
        return $this->hasMany(RmpMethodologyDetail::class, 'rm_plan_id');
    }
}
