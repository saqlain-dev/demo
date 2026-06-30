<?php

namespace App\Models\Campaign;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class,'campaign_id');
    }

    public function emailCampaign(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'email_campaign_for');
    }

    public function emailSender(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'sender');
    }
}
