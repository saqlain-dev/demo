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

class RiskRegister extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function riskRegisterDetails(): HasMany
    {
        return $this->hasMany(RiskRegisterDetail::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }
}
