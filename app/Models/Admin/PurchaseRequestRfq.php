<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Invoice;
use App\Models\RfqWaiver;
use App\Models\TypeValue; 
use App\Traits\LogEvents;  
use App\Models\DisposeRequest;
use App\Models\ProjectAwarded;
use App\Models\PurchaseRequest;
use App\Models\VendorQuotation; 
use App\Models\WorkOrder\WorkOrder;
use App\Models\VendorQuotationDocument;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\PurchaseRequestRFQLog;
use App\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Database\Eloquent\SoftDeletes;  
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\VendorRecommendation;

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

    public function committee(): HasMany
    {
        return $this->hasMany(RfqCommittee::class,'pr_rfq_id');
    }

    public function rfqMinutesOfMeeting(): HasMany
    {
        return $this->hasMany(RfqMinutesOfMeeting::class,'purchase_rfq_id');
    }

    public function rfqWaiver(): HasMany
    {
        return $this->hasMany(RfqWaiver::class,'purchase_rfq_id');
    }
    public function purchaseRequestRFQLogs(): HasMany
    {
        return $this->hasMany(PurchaseRequestRFQLog::class,'purchase_request_rfq_id');
    }


    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class,'rfq_id');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class,'rfq_id');
    }

    public function consultantOrder(): HasOne
    {
        return $this->hasOne(ConsultantContract::class,'rfq_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class,'rfq_id');
    }
    
    public function vendorRecommendations()
    {
        return $this->hasMany(VendorRecommendation::class, 'rfq_id');
    }
}
