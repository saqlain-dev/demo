<?php

namespace App\Models\Admin\Fleet;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Fleet\FleetFeedBack;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin\VehicleRequestInvoiceDocument;
use App\Models\Admin\VehicleRequestVendor;
use App\Models\Admin\VendorVehicleReqQuotation;

class VehicleRequest extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function VehicleId():BelongsTo
    {
        return $this->belongsTo(Vehicle::class,'vehicle_id');
    }

    public function VehicleRequestDetail():HasMany
    {
        return $this->hasMany(VehicleRequestDetail::class, 'vehicle_request_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function vehicleReqVendor(): HasMany
    {
        return $this->hasMany(VehicleRequestVendor::class, 'vehicle_req_id');
    }

    public function feedBack(): HasMany
    {
        return $this->hasMany(FleetFeedBack::class,'requisition_id');
    }

    public function vehicleReqQuotations(): HasMany
    {
        return $this->hasMany(VendorVehicleReqQuotation::class, 'vehicle_req_id');
    }
    public function vrInvoice(): HasOne
    {
        return $this->hasOne(VehicleRequestInvoiceDocument::class, 'vehicle_req_id');
    }

}
