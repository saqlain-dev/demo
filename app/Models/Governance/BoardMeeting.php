<?php

namespace App\Models\Governance;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardMeeting extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function boardMeetingApplicant(): HasMany
    {
        return $this->hasMany(BoardMeetingApplicant::class,'board_meeting_id');
    }

    public function BoardMeetingMom(): HasOne
    {
        return $this->hasOne(MinuteOfMeeting::class, 'board_meeting_id');
    }

    public function agendaDetail(): BelongsTo
    {
        return $this->belongsTo(BoardMeetingAgenda::class,'agenda_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name','employee_id']);
    }
}
