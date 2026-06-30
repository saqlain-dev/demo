<?php

namespace App\Models\Admin;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfqType extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];
}
