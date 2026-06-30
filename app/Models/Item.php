<?php

namespace App\Models;

use App\Models\Admin\ItemVariant;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function itemCategory():BelongsTo
    {
        return $this->belongsTo(ItemCategory::class,'category_id');
    }
    public function subCategory():BelongsTo
    {
        return $this->belongsTo(ItemSubCategory::class,'sub_category_id');
    }

    public function createdUser():BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function itemUnit():BelongsTo
    {
        return $this->belongsTo(ItemUnit::class,'unit_id');
    }
    public function itemType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'item_type');
    }

    public function itemVariants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'item_id');
    }
}
