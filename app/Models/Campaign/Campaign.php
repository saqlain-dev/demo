<?php

namespace App\Models\Campaign;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function campaignStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'campaign_status');
    }
    public function campaignType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'campaign_type');
    }

    public function campaignDetail(): HasMany
    {
        return $this->hasMany(CampaignDetail::class,'campaign_id');
    }

    public function emailCampaign(): HasMany
    {
        return $this->hasMany(EmailCampaign::class,'campaign_id');
    }
}
