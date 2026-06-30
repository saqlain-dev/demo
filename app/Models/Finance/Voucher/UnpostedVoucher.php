<?php

namespace App\Models\Finance\Voucher;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnpostedVoucher extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function UnpostedVoucherDetail(): HasMany
    {
        return $this->hasMany(UnpostedVoucherDetail::class,'unposted_voucher_id');
    }
}
