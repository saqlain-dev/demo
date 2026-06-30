<?php

namespace App\Models\Finance\PaymentVoucher;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucherDetail extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function AccountId():BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id');
    }

    public function PaymentVoucherId():BelongsTo
    {
        return $this->belongsTo(PaymentVoucher::class,'payment_voucher_id');
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
