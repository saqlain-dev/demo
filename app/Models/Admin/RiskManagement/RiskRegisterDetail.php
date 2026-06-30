<?php

namespace App\Models\Admin\RiskManagement;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskRegisterDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function riskRegister(): BelongsTo
    {
        return $this->belongsTo(RiskRegister::class, 'risk_register_id');
    }
    
    public function riskCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_category_id');
    }

    public function riskProbability(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_probability_id');
    }

    public function riskImpact(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_impact_id');
    }

    public function overallRisk(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'overall_risk_id');
    }

    public function riskApproach(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_approach_id');
    }

    public function riskStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'risk_status_id');
    }
    
}
