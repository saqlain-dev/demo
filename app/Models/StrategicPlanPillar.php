<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrategicPlanPillar extends Model
{
    protected $guarded = ['id'];
    use SoftDeletes, HasFactory, LogEvents;
    public function indicators(): HasMany
    {
        return $this->hasMany(StrategicPlanIndicator::class);
    }

}
