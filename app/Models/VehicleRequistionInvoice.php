<?php

namespace App\Models;

use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\VendorVehicleReqQuotation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleRequistionInvoice extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function invoice() {
        return $this->belongsTo(Invoice::class,'invoice_id');
    }
    public function quotation() {
        return $this->belongsTo(VendorVehicleReqQuotation::class,'quotation_id');
    }
    public function vr() {
        return $this->belongsTo(VehicleRequest::class,'vr_id');
    }
}
