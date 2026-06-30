<?php

namespace App\Models\Finance;

use App\Models\Configuration\AllowanceDeduction;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryAccountHeadSetting extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'account_head_id');
    }

    public function allowance_deduction(): BelongsTo
    {
        return $this->belongsTo(AllowanceDeduction::class,'allowance_deduction_id');
    }
}
