<?php

namespace App\Models\Finance\Estimate;

use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Invoice;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetEstimate extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded=['id'];
    public function refferenceable()
    {
        return $this->morphTo();
    }
    public function budgetEstimateDetail(): HasMany
    {
        return $this->hasMany(BudgetEstimateDetail::class,'budget_estimate_id');
    }

    public function AdminBillId(): BelongsTo
    {
        return $this->belongsTo(AdminInvoice::class, 'admin_bill_id');
    }

    public function AdminInvoiceId(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'admin_invoice_id');
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
