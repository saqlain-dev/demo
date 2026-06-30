<?php

namespace App\Models\Finance;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalaryAccountConfiguration extends Model
{
    use LogEvents;
    protected $guarded=['id'];

    public function ChartOfAccountCode(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_code', 'code');
    }
}
