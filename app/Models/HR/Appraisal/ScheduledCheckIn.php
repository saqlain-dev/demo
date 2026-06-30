<?php

namespace App\Models\HR\Appraisal;

use App\Models\HR\Recruitment\EmployeeWorkplan;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScheduledCheckIn extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function performanceCheckIn(): BelongsTo
    {
        return $this->belongsTo(PerformanceCheckIn::class, 'performance_check_in_id');
    }

    public function employeeWorkplan(): BelongsTo
    {
        return $this->belongsTo(EmployeeWorkplan::class, 'employee_workplan_id');
    }

    public function performanceFactors(): HasMany
    {
        return $this->hasMany(PerformanceFactor::class, 'scheduled_check_in_id');
    }
}
