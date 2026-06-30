<?php

namespace App\Models\Finance\PaymentVoucher;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentVoucher extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function PaymentMode(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'payment_mode');
    }
    public function VoucherType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'voucher_type');
    }

    public function Currency(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'currency');
    }

    public function GeneratedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'generated_by');
    }

    public function CheckedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'checked_by');
    }

    public function AuthorizedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'authorized_by');
    }

    public function VoucherDetail(): HasMany
    {
        return $this->hasMany(PaymentVoucherDetail::class, 'payment_voucher_id');
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
