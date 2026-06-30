<?php

namespace App\Models\Finance\Voucher;

use App\Models\Finance\BankInfo;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalVoucher extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function JournalVoucherDetail(): HasMany
    {
        return $this->hasMany(JournalVoucherDetail::class,'journal_voucher_id');
    }

    public function BankAccount(): BelongsTo
    {
        return $this->belongsTo(BankInfo::class, 'bank_account');
    }
}
