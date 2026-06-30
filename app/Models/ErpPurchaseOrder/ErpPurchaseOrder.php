<?php

namespace App\Models\ErpPurchaseOrder;

use App\Models\Quotation\Quotation;
use App\Models\SalesOrder\SalesOrder;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpPurchaseOrder extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function purchaseOrderDetail(): HasMany
    {
        return $this->hasMany(ErpPurchaseOrderItem::class,'purchase_order_id');
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class,'id','quotation_id');
    }

    public function salesOrder(): HasOne
    {
        return $this->hasOne(SalesOrder::class,'po_id');
    }
}
