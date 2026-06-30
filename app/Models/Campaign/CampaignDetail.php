<?php

namespace App\Models\Campaign;

use App\Models\EmailTemplate;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignDetail extends Model
{
    use LogEvents;
    protected $guarded=['id'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class,'email_template_id');
    }
}
