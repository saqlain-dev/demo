<?php

namespace App\Models\Finance\SubGrants;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubGrantBudget extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function subGrant(): BelongsTo
    {
        return $this->belongsTo(SubGrant::class,'sub_grant_id');
    }
}
