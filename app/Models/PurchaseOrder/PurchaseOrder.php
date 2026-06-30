<?php

namespace App\Models\PurchaseOrder;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Admin\TenderDetail;
use App\Models\GRN;
use App\Models\ProjectAwarded;
use App\Models\PurchaseOrderDetail;
use App\Traits\LogEvents;
use App\Traits\LogsAcknowledgementDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory,LogEvents,SoftDeletes, LogsAcknowledgementDate;
    protected $guarded=['id'];
    public function PoItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderDetail::class,'purchase_order_id');
    }
    public function po_award(): BelongsTo
    {
        return $this->belongsTo(ProjectAwarded::class,'project_award_id');
    }

    public function rfqDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestRfq::class,'rfq_id');
    }
    public function tenderDetails(): BelongsTo
    {
        return $this->belongsTo(Tender::class,'tender_id');
    }
    public function grns(): HasMany
    {
        return $this->hasMany(GRN::class, 'po_id');
    }

    public function acknowledgementHistories()
    {
        return $this->morphMany(\App\Models\AcknowledgementHistory::class, 'model');
    }
}
