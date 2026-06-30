<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\ProcurementDetail;
use App\Models\Admin\Procurement;
use App\Models\Invoice;
use App\Models\Admin\Fleet\FuelRequest;
class InvoiceFuelRequest extends Model
{
    use HasFactory;
        protected $fillable = [
        'invoice_id',
        'procurement_id', 
        'procurement_detail_id',
        'name',
        'fuel_request_id',
        'remarks', 
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
 
    public function fuelRequest()
    {
        return $this->belongsTo(FuelRequest::class);
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
