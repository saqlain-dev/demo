<?php

namespace App\Models\HR\Appraisal;

use App\Http\Controllers\Api\V1\HR\Appraisal\DevelopmentGoalController;
use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformancePlanning extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function supervisorImmediate(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_immediate');
    }

    public function supervisorExtended(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_extended');
    }

    public function keyResponsibilities(): HasMany
    {
        return $this->hasMany(KeyResponsibility::class, );
    }

    public function performanceFactors(): HasMany
    {
        return $this->hasMany(PerformanceFactor::class);
    }
    public function developmentGoals(): HasMany
    {
        return $this->hasMany(DevelopmentGoal::class);
    }
}
