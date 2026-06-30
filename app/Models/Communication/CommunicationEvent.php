<?php

namespace App\Models\Communication;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationEvent extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunicationComment::class, 'event_id');
    }
    public function eventDetails(): HasMany
    {
        return $this->hasMany(CommunicationEventDetail::class, 'event_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(AssignCommunicationEventTask::class, 'event_id');
    }

    public function eventHistory(): HasMany
    {
        return $this->hasMany(CommunicationEventHistory::class, 'communication_event_id');
    }

}
