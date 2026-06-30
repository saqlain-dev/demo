<?php

namespace App\Models;

use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\Invoice\InvoiceAudit;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Procurement;
use App\Models\Program\Project\ProjectProfile;
use App\Models\WorkOrder\WorkOrder;
use App\Models\InvoiceVehicleMaintenanceDetail;
use App\Models\InvoiceAtrDetail;
use App\Traits\LogEvents;
use App\Models\Admin\VehicleMaintenanceForm;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\Fleet\VehicleRequest;
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
    function invoiceAudit() {
        return $this->hasMany(InvoiceAudit::class);
    }
    
    function invoiceVehicleMaintenanceDetail() : HasMany {
        return $this->hasMany(InvoiceVehicleMaintenanceDetail::class);
    }
    function invoiceAtrDetail() : HasMany {
        return $this->hasMany(InvoiceAtrDetail::class);
    }
    public function invoiceFuelRequest()
    {
        return $this->hasOne(InvoiceFuelRequest::class, 'invoice_id');
    }
    
    public function vehicleMaintenanceForm() {
        return $this->belongsTo(VehicleMaintenanceForm::class,'vm_id');
    } 
    public function atr() {
        return $this->belongsTo(AirTravelRequest::class,'atr_id');
    }
    public function workCompletion(): HasMany
    {
        return $this->hasMany(WorkCompletion::class,'invoice_id');
    }

    public function vr(): BelongsTo
    {
        return $this->belongsTo(VehicleRequest::class, 'vr_id');
    }

    function invoiceVRDetail() : HasMany {
        return $this->hasMany(VehicleRequistionInvoice::class);
    }

    public function eventManagement() : BelongsTo {
        return $this->belongsTo(EventManagement::class,'event_management_id');
    }

    public function invoiceEventManagementDetail() : HasMany {
        return $this->hasMany(EventMangementInvoice::class);
    }

}
