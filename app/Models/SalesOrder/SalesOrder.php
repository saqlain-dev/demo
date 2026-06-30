<?php

namespace App\Models\SalesOrder;

use App\Models\Customer;
use App\Models\ErpPurchaseOrder\ErpPurchaseOrder;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'order_type');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(ErpPurchaseOrder::class,'po_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class,'sales_order_id');
    }
}
