<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Admin\ProcurementDetail;
class PurchaseRequestDetail extends Model
{
    use HasFactory, LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function items(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function purchase_request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
    public function procurementDetail(): BelongsTo
    {
        return $this->belongsTo(ProcurementDetail::class,'procurement_detail_id');
    }
}
