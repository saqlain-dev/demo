<?php

namespace App\Models\Quotation;

use App\Models\Comment;
use App\Models\Customer;
use App\Models\ErpPurchaseOrder\ErpPurchaseOrder;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\RFP\Rfp;
use App\Models\Supplier\Supplier;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded=['id'];

    public function quotationStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'quotation_status');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class,'lead_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class,'opportunity_id');
    }

    public function quotationDetail(): HasMany
    {
        return $this->hasMany(QuotationDetail::class,'quotation_id');
    }

    public function termCondition(): HasOne
    {
        return $this->hasOne(QuotationTermCondition::class,'quotation_id')->where('letter_type', 1);
    }
    public function proposal(): HasOne
    {
        return $this->hasOne(QuotationTermCondition::class,'quotation_id')->where('letter_type', 2);
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(ErpPurchaseOrder::class,'quotation_id');
    }
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function rfp(): BelongsTo
    {
        return $this->belongsTo(Rfp::class,'rfp_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name','employee_id']);
    }
}
