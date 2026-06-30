<?php

namespace App\Models\Program\Project\MnE;

use App\Models\StrategicPlan;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MnePlanDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $with = ['created_by', 'updated_by', 'plan'];

    public function MnePlanDetailMovs(): HasMany
    {
        return $this->hasMany(MnePlanDetailMov::class);
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProjectMnePlan::class, 'plan_id');
    }

    public function indicatorParent(): BelongsTo
    {
        return $this->belongsTo();
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'unit_of_measure');
    }
    public function dataCollectionFreq(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'data_collection_freq');
    }
    public function dataReportingFreq(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'data_reporting_freq');
    }

    protected $casts = [
        'disaggregates' => 'array',
        'mne_tools' => 'array',
        'required_movs' => 'array',
    ];
}
