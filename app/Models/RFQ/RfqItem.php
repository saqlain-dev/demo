<?php

namespace App\Models\RFQ;

use App\Models\ErpConfiguration\ErpItem;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqItem extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function item(): BelongsTo
    {
        return $this->belongsTo(ErpItem::class,'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'uom');
    }
}
