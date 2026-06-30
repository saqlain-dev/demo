<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OffboardingQuestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_value_id',
        'question',
        'type',
    ];

    public function typeValue() :BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'type_value_id');
    }

    public function answers()
    {
        return $this->hasMany(OffboardingAnswer::class);
    }
}
