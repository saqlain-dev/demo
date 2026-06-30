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

class DepartmentalObjective extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
    public function DesignationId():BelongsTo
    {
        return $this->belongsTo(Designation::class,'designation_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(AppriasalKpi::class, 'departmental_objective_id');
    }
}
