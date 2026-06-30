<?php

namespace App\Models\Governance;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinuteOfMeeting extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function boardMeetingDetail(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class,'board_meeting_id');
    }

    public function boardMeetingApplicants(): HasMany
    {
        return $this->hasMany(BoardMeetingApplicant::class,'board_meeting_id','board_meeting_id');
    }
}
