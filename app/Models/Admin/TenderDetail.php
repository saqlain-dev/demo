<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenderDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }


    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
