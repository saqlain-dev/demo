<?php

namespace App\Models\HR\Complaint;

use App\Models\ComplaintCommittee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplaintMeeting extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded=['id'];

    public function committeeMembers(): HasMany
    {
        return $this->HasMany(ComplaintCommittee::class,'complaint_meeting_id');
    }
}
