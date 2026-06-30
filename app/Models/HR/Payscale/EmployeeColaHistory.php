<?php

namespace App\Models\HR\Payscale;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeColaHistory extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

}
