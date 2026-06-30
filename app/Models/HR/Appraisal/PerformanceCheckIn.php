<?php

namespace App\Models\HR\Appraisal;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceCheckIn extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function scheduledCheckIns(): HasMany
    {
        return $this->hasMany(ScheduledCheckIn::class,'performance_check_in_id');
    }
}
