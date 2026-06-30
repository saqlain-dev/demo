<?php

namespace App\Models\RFP;

use App\Models\Opportunity\Opportunity;
use App\Models\Quotation\Quotation;
use App\Models\RFQ\Rfq;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfp extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function rfpStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'rfp_status');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class,'opportunity_id');
    }

    public function rfpDetail(): HasMany
    {
        return $this->hasMany(RfpItem::class,'rfp_id');
    }

    public function rfq(): HasMany
    {
        return $this->hasMany(Rfq::class,'rfp_id');
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class,'rfp_id');
    }


}
