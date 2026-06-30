<?php

namespace App\Models\Admin;

use App\Models\Admin\Asset\FixedAsset;
use App\Models\Admin\Asset\FixedAssetDepreciation;
use App\Models\Employee;
use App\Models\Item;
use App\Models\Program\Project\ProjectProfile;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\TypeValue;
use App\Models\Vendor;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemVariant extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function RackId(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'rack_id');
    }
    public function store(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'store_id');
    }
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function assignToEmploy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assign_to_emp');
    }

    public function assignToDept(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'assign_to_dept');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }


    public function fixedAsset()
    {
        return $this->hasOne(FixedAsset::class, 'item_variant_id');
    }
    public function depreciations()
    {
        return $this->hasManyThrough(
            FixedAssetDepreciation::class,
            FixedAsset::class,
            'item_variant_id',     // Foreign key on FixedAsset table
            'fixed_asset_id',      // Foreign key on Depreciation table
            'id',                  // Local key on ItemVariant
            'id'                   // Local key on FixedAsset
        );
    }
    public function directDepreciations()
    {
        return $this->hasMany(FixedAssetDepreciation::class, 'item_variant_id', 'id');
    }

}
