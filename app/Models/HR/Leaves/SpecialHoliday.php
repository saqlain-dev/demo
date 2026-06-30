<?php

namespace App\Models\HR\Leaves;

use App\Models\BranchOffice;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpecialHoliday extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function religionDetail(): BelongsTo
    {
        return  $this->belongsTo(TypeValue::class,'religion');
    }

    public function branch_office_detail(): BelongsTo
    {
        return  $this->belongsTo(BranchOffice::class,'branch_office');
    }
}
