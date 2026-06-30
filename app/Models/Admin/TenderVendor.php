<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\ProjectAwarded;
use App\Models\PurchaseRequest;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderVendor extends Model
{
    protected $guarded = ['id'];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }
}
