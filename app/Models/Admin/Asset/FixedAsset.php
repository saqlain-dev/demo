<?php

namespace App\Models\Admin\Asset;

use App\Models\Admin\ItemVariant;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAsset extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function itemVariant()
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }

    public function depreciations()
    {
        return $this->hasMany(FixedAssetDepreciation::class, 'fixed_asset_id');
    }

}
