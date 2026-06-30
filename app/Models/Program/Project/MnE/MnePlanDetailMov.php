<?php

namespace App\Models\Program\Project\MnE;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MnePlanDetailMov extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
}
