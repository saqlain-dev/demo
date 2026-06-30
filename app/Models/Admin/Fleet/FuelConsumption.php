<?php

namespace App\Models\Admin\Fleet;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelConsumption extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];
}
