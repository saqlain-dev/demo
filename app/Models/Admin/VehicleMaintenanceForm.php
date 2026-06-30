<?php

namespace App\Models\Admin;

use App\Models\Admin\Fleet\Vehicle;
use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Invoice;
use App\Models\Admin\ProcurementDetail;
use App\Models\Admin\Procurement;
use App\Models\Item;

class VehicleMaintenanceForm extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function items(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceDetail::class, 'parent_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }

    public function vehicleMaintenanceVendor(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceVendor::class, 'vehicle_maintenance_id');
    }
    public function vehicleMaintenanceQuotations(): HasMany
    {
        return $this->hasMany(VendorVehMaintenanceQuot::class, 'vehicle_maintenance_id');
    }

    public function vmInvoice(): HasOne
    {
        return $this->hasOne(VehicleMaintenanceInvoiceDocument::class, 'vehicle_maintenance_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'vm_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function procurement()
    {
        return $this->belongsTo(Procurement::class);
    } 
    public function procurementDetail()
    {
        return $this->belongsTo(ProcurementDetail::class);
    }

}
