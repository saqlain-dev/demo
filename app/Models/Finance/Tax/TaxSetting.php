<?php

namespace App\Models\Finance\Tax;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxSetting extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_type');
    }
    public function taxComputation(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_computation');
    }
    public function taxScope(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_scope');
    }
    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_group');
    }
}
