<?php

namespace App\Models\WorkOrder;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Invoice;
use App\Traits\LogEvents;
use App\Traits\LogsAcknowledgementDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use LogEvents, SoftDeletes,LogEvents, LogsAcknowledgementDate;
    protected $guarded = ['id'];
    public function WoItems(): HasMany
    {
        return $this->hasMany(WorkOrderDetail::class,'work_order_id');
    }

    public function tenderDetail(): BelongsTo
    {
        return $this->belongsTo(Tender::class,'tender_id');
    }
    public function rfqDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestRfq::class,'rfq_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class,'work_order_id');
    }

    public function acknowledgementHistories()
    {
        return $this->morphMany(\App\Models\AcknowledgementHistory::class, 'model');
    }
}
