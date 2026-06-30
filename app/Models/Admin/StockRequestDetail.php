<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockRequestDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function StockRequestId(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class, 'stock_request_id');
    }
    public function ItemCategoryId(): BelongsTo
    {

        return $this->belongsTo(Item::class, 'item_category_id');

    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

}
