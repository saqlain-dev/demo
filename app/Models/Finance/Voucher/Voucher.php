<?php

namespace App\Models\Finance\Voucher;

use App\Models\Finance\Audit\AuditTrail;
use App\Models\Finance\BankInfo;
use App\Models\Finance\Tax\TaxFilingStatus;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function voucherDetail(): HasMany
    {
        return $this->hasMany(VoucherDetail::class,'voucher_id');
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(GeneralLedger::class,'voucher_no', 'id');
    }

    public function BankAccount():BelongsTo
    {
        return $this->belongsTo(BankInfo::class,'bank_account');
    }

    public function reverseVoucher(): HasOne
    {
        return $this->hasOne(VoucherDetail::class, 'VoucherFromID', 'id')
            ->where('VoucherFrom', 'JV');
    }



    public function jvVoucher(): HasOne
    {
        return $this->hasOne(VoucherDetail::class, 'voucher_id');
    }

    public function taxFilling(): HasMany
    {
        return $this->hasMany(TaxFilingStatus::class,'voucher_id');
    }

    public function auditTrail(): HasMany
    {
        return $this->hasMany(AuditTrail::class,'voucher_id');
    }

    public function voucherAttachments(): HasMany
    {
        return $this->hasMany(VoucherAttachment::class,'voucher_id');
    }






}
