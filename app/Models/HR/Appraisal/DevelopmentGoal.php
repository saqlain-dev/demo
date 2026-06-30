<?php

namespace App\Models\HR\Appraisal;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DevelopmentGoal extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];
}
