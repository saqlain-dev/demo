<?php

namespace App\Models\HR\Appraisal;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceFactor extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function performanceFactorValue(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'performance_factor_value');
    }

    public function performancePlanning(): BelongsTo
    {
        return $this->belongsTo(PerformancePlanning::class, 'performance_planning_id');
    }

    public function questionSection(): BelongsTo
    {
        return $this->belongsTo(SectionQuestion::class, 'question_id');
    }
    public function appriasalKpi(): BelongsTo
    {
        return $this->belongsTo(AppriasalKpi::class, 'section_id');
    }
}
