<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\VehicleMaintenanceForm;
use App\Models\Invoice;
use  App\Models\Admin\VendorVehMaintenanceQuot;
class InvoiceVehicleMaintenanceDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'vm_id',
        'nature_of_work',
        'qty',
        'remarks',
        'estimated_unit_cost',
        'amount',
        'quotation_id',
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    public function quotation() {
        return $this->belongsTo(VendorVehMaintenanceQuot::class);
    }

    public function vehicleMaintenanceForm() {
        return $this->belongsTo(VehicleMaintenanceForm::class,'vm_id');
    }
    

}
