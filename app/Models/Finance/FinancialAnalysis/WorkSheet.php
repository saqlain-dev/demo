<?php

namespace App\Models\Finance\FinancialAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkSheet extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'financial_analysis_work_sheets';

    public function resultDocument(): HasOne
    {
        return $this->hasOne(ResultDocument::class, 'worksheet_id');
    }

    public function managementReport(): HasOne
    {
        return $this->hasOne(ManagementReport::class, 'worksheet_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->select(['id', 'name']);
    }
}
