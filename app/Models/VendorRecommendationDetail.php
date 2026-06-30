<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRecommendationDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function vendorRecommendation()
    {
        return $this->belongsTo(VendorRecommendation::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
