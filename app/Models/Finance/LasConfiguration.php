<?php

namespace App\Models\Finance;

use App\Models\Finance\BankInfo;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasConfiguration extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function bankInfo(): HasMany
    {
        return $this->hasMany(BankInfo::class,'las_configuration_id', 'id');
    }
}
