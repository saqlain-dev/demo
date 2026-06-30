<?php

namespace App\Models;

use App\Models\Admin\Procurement;
use App\Models\Admin\ProcurementDetail;
use App\Models\Communication\CommunicationEvent;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventManagement extends Model
{
    use SoftDeletes, HasFactory, LogEvents;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(TypeValue::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function procurement()
    {
        return $this->belongsTo(Procurement::class, 'procurement_id');
    }

    public function eventMangementDetails(){
        return $this->hasMany(EventManagementDetail::class,'event_management_id');
    }

    public function eventManagementReqVendor(): HasMany
    {
        return $this->hasMany(EventManagementVendor::class, 'event_management_id');
    }

    public function eventManagementquotations(): HasMany
    {
        return $this->hasMany(VendorEventManagmentQuotation::class, 'event_management_id');
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class, 'event_management_id');
    }

    public function procurementDetail()
    {
        return $this->belongsTo(ProcurementDetail::class, 'procurement_detail_id');
    }

    public function eventTasks(): HasMany
    {
        return $this->hasMany(EventTask::class, 'event_management_id');
    }

    public function eventCommunications(): HasMany
    {
        return $this->hasMany(CommunicationEvent::class, 'event_management_id');
    }

    public function branchOffice()
    {
        return $this->belongsTo(BranchOffice::class, 'branch_office_id');
    }

}
