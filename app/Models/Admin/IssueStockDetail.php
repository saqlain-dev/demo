<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueStockDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function IssueStockId(): BelongsTo
    {
        return $this->belongsTo(IssueStock::class, 'issue_stock_id');
    }
    public function ItemId(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function variantsDetail()
    {
        $ids = explode(',', $this->variant_ids);
        $ids = array_map('intval', $ids);
        //return ItemVariant::whereIn('id', $ids)->get();
        return ItemVariant::select('item_variants.*', 'location.name as location_name','store.name as store_name')
            ->join('locations as location', 'item_variants.location_id', '=', 'location.id')
            ->join('locations as store', 'item_variants.store_id', '=', 'store.id')
            ->whereIn('item_variants.id', $ids)
            ->whereNull('item_variants.deleted_at')
            ->get();
    }
}
