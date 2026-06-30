<?php

namespace App\Models\Admin;

use App\Models\Admin\Library\BookReconciliationDetail;
use App\Models\Item;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuctionGatePass extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function prRfq(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestRfq::class, 'purchase_request_rfq_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

}
