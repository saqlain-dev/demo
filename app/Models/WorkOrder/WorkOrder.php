<?php

namespace App\Models\WorkOrder;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use LogEvents, SoftDeletes,LogEvents;
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
}
