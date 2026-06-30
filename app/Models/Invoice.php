<?php

namespace App\Models;

use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Program\Project\ProjectProfile;
use App\Models\WorkOrder\WorkOrder;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class,'invoice_id');
    }

    public function ProjectId(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }
    public function vendorDetail(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'supplier_id');
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class,'grn_id');
    }

    public function consultantContract(): BelongsTo
    {
        return $this->belongsTo(ConsultantContract::class);
    }
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequestRfq::class, 'rfq_id');
    }

}
