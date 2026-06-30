<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrnItem extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function grnItem(): HasMany
    {
        return $this->hasMany(GRN::class,'grn_id');
    }

    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class, 'grn_id');
    }
}
