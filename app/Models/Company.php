<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use LogEvents, SoftDeletes;
	protected $guarded = ['id'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'currency_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class,'country_id');
    }
}
