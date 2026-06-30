<?php

namespace App\Models\HR;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreGrossSalaryAllowances extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'allowance_type','id');
    }
}
