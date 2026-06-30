<?php

namespace App\Models\Communication;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Communication\TeamCommunicationComment;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommunicationEventDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(EventSubCategory::class, 'sub_category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(CommunicationEvent::class, 'event_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TeamCommunicationComment::class, 'task_id');
    }

    public function eventDetailHistory(): HasMany
    {
        return $this->hasMany(CommunicationEventHistory::class);
    }


}
