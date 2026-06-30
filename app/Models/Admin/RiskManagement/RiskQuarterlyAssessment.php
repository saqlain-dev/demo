<?php

namespace App\Models\Admin\RiskManagement;

use App\Models\Admin\FinancialYear;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskQuarterlyAssessment extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
    

    public function createdBy(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }

    public function riskProbability(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'risk_probability_id');
    }

    public function riskImpact(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_impact_id');
    }

    public function overallRisk(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'overall_risk_id');
    }
}
