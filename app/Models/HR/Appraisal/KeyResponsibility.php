<?php

namespace App\Models\HR\Appraisal;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeyResponsibility extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function performancePlanning(): BelongsTo
    {
        return $this->belongsTo(PerformancePlanning::class, 'performance_planning_id');
    }

    public function questionSection(): BelongsTo
    {
        return $this->belongsTo(SectionQuestion::class, 'question_id');
    }
}
