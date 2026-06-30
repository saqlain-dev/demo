<?php

namespace App\Models;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GRN extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function grnItem(): HasMany
    {
        return $this->hasMany(GrnItem::class,'grn_id');
    }

    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function grnInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class,'grn_id');
    }

    public function poDetails(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class,'po_id');
    }
}
