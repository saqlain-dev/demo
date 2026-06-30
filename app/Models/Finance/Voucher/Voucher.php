<?php

namespace App\Models\Finance\Voucher;

use App\Models\Finance\BankInfo;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(GeneralLedger::class,'voucher_no', 'id');
    }

    public function BankAccount():BelongsTo
    {
        return $this->belongsTo(BankInfo::class,'bank_account');
    }
}
