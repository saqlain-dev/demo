<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrategicPlanIndicatorYear extends Model
{
    protected $guarded = ['id'];
    use SoftDeletes, HasFactory, LogEvents;
}
