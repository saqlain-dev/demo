<?php

namespace App\Models\Finance\CustomerInvoice;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoiceDetail extends Model
{
    use HasFactory,LogEvents;
    protected $guarded=['id'];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'item_coa')->select('id','name','code');
    }

}
