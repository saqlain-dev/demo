<?php

namespace App\Models\Governance;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardMeetingApplicant extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function applicantDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'board_member_id');
    }
}
