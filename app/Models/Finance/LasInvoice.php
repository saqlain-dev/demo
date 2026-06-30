<?php

namespace App\Models\Finance;

use App\Models\Donar\DonarProfile;
use App\Models\Finance\Grants\Nofo;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasInvoice extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function InvoiceDetail(): HasMany
    {
        return $this->hasMany(LasInvoiceDetail::class, 'las_invoice_id');
    }

    public function BankId(): BelongsTo
    {
        return $this->belongsTo(BankInfo::class,'bank_id');
    }
    public function customerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function nofo(): BelongsTo
    {
        return $this->belongsTo(Nofo::class, 'nofo_id');
    }
}
