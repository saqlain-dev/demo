<?php

namespace App\Models\Finance\Voucher;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnpostedVoucherDetail extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];
}
