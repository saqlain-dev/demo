<?php

namespace App\Models\Finance\FinanceBill;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\Estimate\BudgetEstimateDetail;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceBillDetail extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function BudgetEstimateDetailId():BelongsTo
    {
        return $this->belongsTo(BudgetEstimateDetail::class,'budget_estimate_detail_id');
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'item_coa')->select('id','name','code');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
