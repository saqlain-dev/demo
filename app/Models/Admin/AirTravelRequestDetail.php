<?php

namespace App\Models\Admin;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirTravelRequestDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AirTravelRequest::class, 'parent_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }
}
