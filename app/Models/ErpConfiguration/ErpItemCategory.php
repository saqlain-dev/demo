<?php

namespace App\Models\ErpConfiguration;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ErpItemCategory extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function itemSubcategory(): HasMany
    {
        return $this->hasMany(ErpItemSubCategory::class,'category_id');
    }

    public function categoryDepartment(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department_id');
    }
}
