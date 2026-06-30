<?php

namespace App\Models\Admin;

use App\Models\TypeValue;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorAtrQuotation extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'airline');
    }
    public function airlineCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'airline_category');
    }
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }
}
