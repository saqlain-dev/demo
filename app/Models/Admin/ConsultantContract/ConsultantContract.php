<?php

namespace App\Models\Admin\ConsultantContract;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\WorkOrder\WorkOrderDetail;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultantContract extends Model
{
    use LogEvents, SoftDeletes,LogEvents;
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
}
