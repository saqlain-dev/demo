<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventMangementInvoice extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function eventManagement(): BelongsTo
    {
        return $this->belongsTo(EventManagement::class,'event_management_id');
    }
    public function eventManagementDetails(): BelongsTo
    {
        return $this->belongsTo(EventManagementDetail::class,'event_management_details_id');
    }
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function quotation() {
        return $this->belongsTo(VendorEventManagmentQuotation::class,'quotation_id');
    }
}
