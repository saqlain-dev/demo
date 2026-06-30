<?php

namespace App\Models;

use App\Models\Admin\PurchaseRequestRfq;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function Supplier(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'supplier_id');
    }
    public function serviceProvider(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'supplier_id');
    }

    public function rfqs(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseRequestRfq::class, 'pr_rfq_vendors','vendor_id','purchase_request_rfq_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'vendor_id');
    }
}
