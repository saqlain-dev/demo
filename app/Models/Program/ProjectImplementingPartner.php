<?php

namespace App\Models\Program;

use App\Models\Finance\CustomerHub;
use App\Models\Finance\LasInvoice;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectImplementingPartner extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function OrgType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'org_type');
    }
    public function customerable()
    {
        return $this->morphMany(CustomerHub::class, 'customerAble');
    }

    public function lasInvoices(): MorphMany
    {
        return $this->morphMany(LasInvoice::class, 'customerable');
    }

}
