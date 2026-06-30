<?php

namespace App\Models\Communication;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationEventHistory extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function communicationEvent(): BelongsTo
    {
        return $this->belongsTo(CommunicationEvent::class, 'communication_event_id');
    }
    public function communicationEventDetail(): BelongsTo
    {
        return $this->belongsTo(CommunicationEventDetail::class, 'communication_event_detail_id');
    }

}
