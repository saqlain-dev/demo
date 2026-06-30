<?php

namespace App\Models\Supplier;

use App\Models\Country;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function supplierGroup(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'supplier_group');
    }

    public function supplierType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'supplier_type');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }
}
