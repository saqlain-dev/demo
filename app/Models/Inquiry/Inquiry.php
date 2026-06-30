<?php

namespace App\Models\Inquiry;

use App\Models\Lead;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function inquiryType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'inquiry_type');
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class,'inquiry_id');
    }
}
