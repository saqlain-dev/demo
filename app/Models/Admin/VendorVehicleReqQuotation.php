<?php

namespace App\Models\Admin;

use App\Models\Admin\Fleet\VehicleRequestDetail;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorVehicleReqQuotation extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function VehicleRequestDetail():BelongsTo
    {
        return $this->belongsTo(VehicleRequestDetail::class, 'vehicle_req_detail_id');
    }
}
