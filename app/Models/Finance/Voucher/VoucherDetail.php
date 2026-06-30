<?php

namespace App\Models\Finance\Voucher;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VoucherDetail extends Model
{
    use HasFactory;

    protected $guarded=['id'];

    public function jvVoucherDetail(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'VoucherFromID', 'id')
            ->where('VoucherType', 'JV');
    }

    public function voucherDetail(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }
}
