<?php

namespace App\Models;

use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\WorkOrder\WorkOrder;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAwarded extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function awardable(): MorphTo
    {
        return $this->morphTo();
    }
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function awardPo(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class,'project_award_id')->latestOfMany();
    }
    public function awardWo(): HasOne
    {
        return $this->hasOne(WorkOrder::class,'project_award_id')->latestOfMany();
    }
    public function awardCc(): HasOne
    {
        return $this->hasOne(ConsultantContract::class,'project_award_id')->latestOfMany();
    }
    public function awardQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class,'quotation_id');
    }
    
}
