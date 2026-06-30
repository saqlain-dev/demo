<?php

namespace App\Models\Finance\Audit;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrailLog extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];
}
