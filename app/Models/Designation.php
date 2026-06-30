<?php

namespace App\Models;

use App\Models\Configuration\AllowanceDeduction;
use App\Models\Configuration\PositionWiseAllowDeduct;
use App\Models\HR\Appraisal\KpiIndicatorsMapping;
use App\Models\HR\Payscale\Payscale;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    //protected $with = ['created_by','updated_by'];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function reportTo(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'report_to');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id')->select(['id','name','designation_id','emp_profile','department_id','employee_no']);
    }
    public function users()
    {
        return $this->hasMany(User::class, 'designation_id')->where('status',1);
    }

    public function manager()
    {
        return $this->belongsTo(Designation::class, 'report_to');
    }

    public function allowanceDeduction(): HasMany
    {
        return $this->hasMany(PositionWiseAllowDeduct::class,'position_id');
    }

    public function payScale(): HasMany
    {
        return $this->hasMany(Payscale::class,'position');
    }

    public function kpiIndicatorsMappings(): BelongsToMany
    {
        return $this->belongsToMany(KpiIndicatorsMapping::class, 'designation_kpi_indicator_mapping', 'designation_id', 'kpi_indicator_mapping_id');
    }

}
