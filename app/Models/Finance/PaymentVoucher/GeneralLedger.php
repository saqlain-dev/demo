<?php

namespace App\Models\Finance\PaymentVoucher;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralLedger extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];
}
