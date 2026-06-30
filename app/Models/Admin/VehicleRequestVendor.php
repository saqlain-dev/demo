<?php

namespace App\Models\Admin;

use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequestVendor extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
}
