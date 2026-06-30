<?php

namespace App\Models\Finance\CustomerInvoice;

use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded=['id'];

    public function customerInvoiceDetail(): HasMany
    {
        return $this->hasMany(CustomerInvoiceDetail::class,'customer_invoice_id');
    }


}
