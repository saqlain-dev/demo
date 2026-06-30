<?php

namespace App\Models\Admin;

use App\Models\DisposeRequest;
use App\Models\Item;
use App\Models\ProjectAwarded;
use App\Models\User;
use App\Models\VendorQuotation;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Models\TypeValue;
use App\Models\Vendor;
use App\Models\VendorQuotationDocument;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequestRfq extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    protected $casts = [
        'documents_ids' => 'array'
    ];

    public function vendors(): HasMany
    {
        return $this->hasMany(PrRfqVendor::class, 'purchase_request_rfq_id');
    }

    public function rfqVendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'pr_rfq_vendors', 'purchase_request_rfq_id', 'vendor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequestRfqDetail::class,'purchase_request_rfq_id');
    }
    public function vendor_quotations()
    {
        return $this->morphMany(VendorQuotation::class, 'projectable');
    }


    public function awardProject(): MorphMany
    {
        return $this->morphMany(ProjectAwarded::class,'awardable');
    }

    public function rfType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'rf_type');
    }

    public function quotationType(): BelongsTo
    {
        return $this->belongsTo(RfqType::class,'quotation_type');
    }

    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function purchase_request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function disposeRequest(): BelongsTo
    {
        return $this->belongsTo(DisposeRequest::class, 'dispose_request_id');
    }

    public function rfqBiddingDocuments(): HasMany
    {
        return $this->hasMany(VendorQuotationDocument::class,'rfq_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }



}
