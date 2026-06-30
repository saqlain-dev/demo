<?php

namespace App\Models\RFP;

use App\Models\Division\Division;
use App\Models\Employee;
use App\Models\ErpConfiguration\ErpItem;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfpItem extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ErpItem::class,'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'uom');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class,'division_id');
    }

    public function erpItemCategory(): BelongsTo
    {
        return $this->belongsTo(ErpItemCategory::class,'erp_category_id');
    }

    public function assignToEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'assign_to');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'brand_id');
    }
}
