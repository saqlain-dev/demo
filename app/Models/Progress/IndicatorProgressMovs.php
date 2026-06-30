<?php

namespace App\Models\Progress;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IndicatorProgressMovs extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
}
