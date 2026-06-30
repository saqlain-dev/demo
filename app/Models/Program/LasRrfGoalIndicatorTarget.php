<?php

namespace App\Models\Program;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasRrfGoalIndicatorTarget extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
}
