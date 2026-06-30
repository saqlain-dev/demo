<?php

namespace App\Models\Admin;

use App\Models\Item;
use App\Models\ProjectAwarded;
use App\Models\PurchaseRequest;
use App\Models\TypeValue;
use App\Models\User;
use App\Models\VendorQuotation;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tender extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function tenderDetails(): HasMany
    {
        return $this->hasMany(TenderDetail::class, 'tender_id');
    }

    public function itemDetail(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function tenderNature(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'nature_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function vendor_quotations()
    {
        return $this->morphMany(VendorQuotation::class, 'projectable');
    }
    public function purchase_request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function awardProject(): MorphMany
    {
        return $this->morphMany(ProjectAwarded::class,'awardable');
    }

    public function tendercommittee(): HasMany
    {
        return $this->hasMany(TenderCommittee::class,'tender_id');
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(TenderVendor::class,'tender_id');
    }
    public function tenderMinutesOfMeeting(): HasMany
    {
        return $this->hasMany(TenderMinutesOfMeeting::class,'tender_id');
    }

}
