<?php

namespace App\Models\HR\Appraisal;

use App\Models\Designation;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiIndicatorsMapping extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function designations(): BelongsToMany
    {
        return $this->belongsToMany(Designation::class, 'designation_kpi_indicator_mapping', 'kpi_indicator_mapping_id', 'designation_id');
    }
    public function kpi():BelongsTo
    {
        return $this->belongsTo(AppriasalKpi::class,'kpi_id');
    }
    
    public function indicator():BelongsTo
    {
        return $this->belongsTo(SectionQuestion::class,'indicator_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
