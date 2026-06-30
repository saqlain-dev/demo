<?php

namespace App\Models\Admin\GDN;

use App\Models\GrnItem;
use App\Models\Invoice;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gdn extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function gdnItem(): HasMany
    {
        return $this->hasMany(GdnItem::class,'gdn_id');
    }

    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function poDetails(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class,'po_id');
    }
}
