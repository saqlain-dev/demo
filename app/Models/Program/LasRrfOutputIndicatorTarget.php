<?php

namespace App\Models\Program;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasRrfOutputIndicatorTarget extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];
}
