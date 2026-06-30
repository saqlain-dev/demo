<?php

namespace App\Models\Configuration;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScaleRating extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'scale_stage');
    }
}
