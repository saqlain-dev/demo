<?php

namespace App\Models\Admin;


use App\Models\DisposeRequest;
use App\Models\DisposeRequestDetail;
use App\Models\Item;
use App\Models\PurchaseRequestDetail;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestRfqDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function purchase_request_item(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestDetail::class, 'purchase_request_detail_id');
    }
    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }
    public function disposeRequestDetail(): BelongsTo
    {
        return $this->belongsTo(DisposeRequestDetail::class,'dispose_request_detail_id');
    }
}
