<?php

namespace App\Models\Admin;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleRequestInvoiceDocument extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
}
