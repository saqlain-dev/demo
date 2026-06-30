<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\PurchaseRequestDetail;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DisposeItem extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestDetail::class, 'po_detail_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

}
