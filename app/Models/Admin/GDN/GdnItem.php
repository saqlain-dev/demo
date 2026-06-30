<?php

namespace App\Models\Admin\GDN;

use App\Models\Item;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GdnItem extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function gdnItem(): HasMany
    {
        return $this->hasMany(Gdn::class,'gdn_id');
    }

    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }
}
