<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function marketSegment(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'market_segment_id');
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'customer_group_id');
    }

    public function industry(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'industry_id');
    }

    public function territory(): BelongsTo
    {
        return $this->belongsTo(Country::class,'territory_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class,'company_id');
    }
}
