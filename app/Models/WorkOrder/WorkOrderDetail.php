<?php

namespace App\Models\WorkOrder;

use App\Models\Item;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrderDetail extends Model
{
    use LogEvents, SoftDeletes,LogEvents;
    protected $guarded = ['id'];
    public function woItems(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
