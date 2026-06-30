<?php

namespace App\Models\ErpConfiguration;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpItemSubCategory extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ErpItemCategory::class,'category_id');
    }
}
