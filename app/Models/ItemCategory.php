<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCategory extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function itemSubcategory(): HasMany
    {
        return $this->hasMany(ItemSubCategory::class,'category_id');
    }
}
