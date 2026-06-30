<?php

namespace App\Models\Donar;

use App\Models\Finance\CustomerHub;
use App\Models\Finance\LasInvoice;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use App\Models\Program\ProjectDonor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DonarProfile extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function OrgType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'org_type');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(ProjectDonor::class, 'donor_id');
    }

    public function customerable()
    {
        return $this->morphMany(CustomerHub::class, 'customerAble');
    }

    // In DonorProfile model
    public function lasInvoices(): MorphMany
    {
        return $this->morphMany(LasInvoice::class, 'customerable');
    }
}
