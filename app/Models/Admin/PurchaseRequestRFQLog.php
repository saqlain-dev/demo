<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Admin\PurchaseRequestRFQ;
class PurchaseRequestRFQLog extends Model
{
    use HasFactory;
    protected $table = 'purchase_request_rfq_logs';  
    protected $fillable = [
        'purchase_request_rfq_id',
        'expiry_date',
        'action',
        'changes',
        'created_by',
    ];

    protected $casts = [
        'changes' => 'array',
        'expiry_date' => 'date',
    ];

    public function rfq()
    {
        return $this->belongsTo(PurchaseRequestRFQ::class, 'purchase_request_rfq_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
