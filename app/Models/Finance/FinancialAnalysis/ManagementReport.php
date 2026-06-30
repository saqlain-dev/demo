<?php

namespace App\Models\Finance\FinancialAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ManagementReport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(WorkSheet::class, 'worksheet_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->select(['id', 'name']);
    }
}
