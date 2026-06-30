<?php

namespace App\Models\Quotation;

use App\Models\Configuration\GeneratedLetter;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuotationTermCondition extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class,'quotation_id');
    }

    public function generatedLetter(): BelongsTo
    {
        return $this->belongsTo(GeneratedLetter::class,'letter_id');
    }
}
