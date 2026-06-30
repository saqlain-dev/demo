<?php

namespace App\Models\Configuration;

use App\Models\Designation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionWiseAllowDeduct extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Designation::class,'position_id');
    }

    public function positionAllowanceDeduction(): BelongsTo
    {
        return $this->belongsTo(AllowanceDeduction::class,'allowance_deduction_id');
    }
}
