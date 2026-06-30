<?php

namespace App\Models\Admin\Asset;

use App\Models\Admin\ItemVariant;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixedAssetDepreciation extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    // In FixedAssetDepreciation model
    public function fixedAsset()
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function register()
    {
        return $this->belongsTo(FixedAssetRegister::class, 'register_id');
    }

    public function itemVariant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class,'item_variant_id');
    }

}
