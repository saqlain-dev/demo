<?php

namespace App\Models\Finance\Estimate;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;

class BudgetEstimateDetail extends Model
{
    use LogEvents;

    protected $guarded=['id'];
}
