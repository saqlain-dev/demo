<?php

namespace App\Models;

use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorRecommendation extends Model
{
    use HasFactory, LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function details()
    {
        return $this->hasMany(VendorRecommendationDetail::class);
    }

    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }

    public function rfq()
    {
        return $this->belongsTo(PurchaseRequestRfq::class, 'rfq_id');
    }
}
