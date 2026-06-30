<?php

namespace App\Models\Finance;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankInfo extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function lasConfiguration(): BelongsTo
    {
        return $this->belongsTo(LasConfiguration::class,'las_configuration_id');
    }

    public function HeadId(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'head_id')->select('id', 'name', 'code');
    }
}
