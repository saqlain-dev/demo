<?php

namespace App\Models\HR\Attendance;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FetchedStatus extends Model
{
    use HasFactory,LogEvents;
    protected $guarded=['id'];
}
