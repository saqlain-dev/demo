<?php

namespace App\Models\Admin\ConsultantContract;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vendor;
use App\Models\WorkOrder\WorkOrderDetail;
use App\Traits\LogEvents;
use App\Traits\LogsAcknowledgementDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantContract extends Model
{
    use LogEvents, SoftDeletes, LogEvents, LogsAcknowledgementDate; 
    protected $guarded = ['id'];
    public function CcItems(): HasMany
    {
        return $this->hasMany(ConsultantContractDetail::class,'consultant_contract_id');
    }

    public function tenderDetail(): BelongsTo
    {
        return $this->belongsTo(Tender::class,'tender_id');
    }
    public function rfqDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestRfq::class,'rfq_id');
    }

    public function vendor(){
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function invoices(){
        return $this->hasMany(Invoice::class,'consultant_contract_id');
    }

    public function acknowledgementHistories()
    {
        return $this->morphMany(\App\Models\AcknowledgementHistory::class, 'model');
    }
}
