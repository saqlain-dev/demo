<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use  App\Models\Admin\VendorAtrQuotation;
use App\Models\Admin\AirTravelRequest;
use App\Models\Invoice;
class InvoiceAtrDetail extends Model
{
    protected $fillable = [
        'invoice_id',
        'atr_id',
        'datetime',
        'traveler',
        'airline',
        'remarks',
        'amount',
        'quotation_id',
    ];

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }
    public function quotation() {
        return $this->belongsTo(VendorAtrQuotation::class);
    }
    public function atr() {
        return $this->belongsTo(AirTravelRequest::class,'atr_id');
    }
}
