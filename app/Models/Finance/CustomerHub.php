<?php

namespace App\Models\Finance;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\ChartOfAccountClass;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerHub extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded=['id'];
    public function customerable(): MorphTo
    {
        return $this->morphTo();
    }

    public function coaDetail(): BelongsTo
    {
       return $this->belongsTo(ChartOfAccount::class,'customer_coa');
    }
    public function coaClassDetail(): BelongsTo
    {
       return $this->belongsTo(HeadClass::class,'class');
    }
}
