<?php

namespace App\Models\Admin\ConsultantContract;

use App\Models\Item;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantContractDetail extends Model
{
    use LogEvents, SoftDeletes,LogEvents;
    protected $guarded = ['id'];
    public function ccItem(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
