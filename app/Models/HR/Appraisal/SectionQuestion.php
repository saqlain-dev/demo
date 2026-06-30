<?php

namespace App\Models\HR\Appraisal;

use App\Models\Designation;
use App\Models\HR\Recruitment\EmployeeWorkplan;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionQuestion extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }
//    public function typeValue(): BelongsTo
//    {
//        return $this->belongsTo(TypeValue::class, 'type_value_id');
//    }

    public function Kpis():BelongsTo
    {
        return $this->belongsTo(AppriasalKpi::class, 'type_value_id');
    }

    public function employeeWorkplans(): BelongsToMany
    {
        return $this->belongsToMany(EmployeeWorkplan::class, 'employee_workplan_section_question');
    }

    public function kpiIndicatorsMappings(): HasMany
    {
        return $this->hasMany(KpiIndicatorsMapping::class, 'indicator_id');
    }

}
