<?php

namespace App\Models\Governance;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardMeetingAgenda extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];
}
