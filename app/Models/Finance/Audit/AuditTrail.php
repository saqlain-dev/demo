<?php

namespace App\Models\Finance\Audit;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrail extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'VerifiedBy');
    }
}
