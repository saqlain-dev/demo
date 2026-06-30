<?php

namespace App\Models\Admin;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqMinutesOfMeeting extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
}
