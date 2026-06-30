<?php

namespace App\Models\HR\Appraisal;

use App\Models\Designation;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppriasalKpi extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
    public function DesignationId():BelongsTo
    {
        return $this->belongsTo(Designation::class,'designation_id');
    }

    public function KpiIndicators():HasMany
    {
        return $this->hasMany(SectionQuestion::class,'type_value_id');
    }
    public function kpiIndicatorsMapping():HasMany
    {
        return $this->hasMany(KpiIndicatorsMapping::class,'kpi_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function departmentalObjective(): BelongsTo
    {
        return $this->belongsTo(DepartmentalObjective::class, 'departmental_objective_id');
    }
    
    public function domain(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'domain_id');
    }
}
