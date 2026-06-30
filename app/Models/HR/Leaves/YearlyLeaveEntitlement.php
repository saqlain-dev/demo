<?php

namespace App\Models\HR\Leaves;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class YearlyLeaveEntitlement extends Model
{
    use HasFactory, LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function leave_type(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'leave_type_id');
    }
}
