<?php

namespace App\Models\Admin;

use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorVehMaintenanceQuot extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
    public function VehicleMaintenanceDetail():BelongsTo
    {
        return $this->belongsTo(VehicleMaintenanceDetail::class, 'vehicle_maint_detail_id');
    }
}
