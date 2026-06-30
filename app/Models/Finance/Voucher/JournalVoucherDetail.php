<?php

namespace App\Models\Finance\Voucher;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalVoucherDetail extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function NominalId(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'nominal_id', 'code');
    }
}
