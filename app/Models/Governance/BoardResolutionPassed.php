<?php

namespace App\Models\Governance;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardResolutionPassed extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function BoardMeetingId(): BelongsTo
    {
        return $this->belongsTo(BoardMeeting::class,'board_meeting_id');
    }
}
