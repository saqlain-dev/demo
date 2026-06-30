<?php

namespace App\Models\ErpConfiguration;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpItem extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function itemCategory():BelongsTo
    {
        return $this->belongsTo(ErpItemCategory::class,'category_id');
    }
    public function subCategory():BelongsTo
    {
        return $this->belongsTo(ErpItemSubCategory::class,'sub_category_id');
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'item_type');
    }
}
