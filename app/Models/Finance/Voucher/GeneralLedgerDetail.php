<?php

namespace App\Models\Finance\Voucher;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedgerDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];
    protected $table = 'tbl_general_ledger_details';

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'NominalID', 'code');
    }
}
