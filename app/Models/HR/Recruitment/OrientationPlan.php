<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\OrientationPlanActivity;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrientationPlan extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
    public function ExecutedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'executed_by','id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function orientationActivity(): HasMany
    {
        return $this->hasMany(OrientationPlanActivity::class,'orientation_plan_id');
    }


}
