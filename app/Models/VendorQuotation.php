<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorQuotation extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];
    public function projectable(): MorphTo
    {
        return $this->morphTo();
    }
    public function quotationItems(): HasMany
    {
        return $this->hasMany(VendorQuotationDetail::class,'quotation_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class,'vendor_id');
    }

    public function projectsDocuments(): HasMany
    {
        return $this->hasMany(VendorQuotationDocument::class,'quotation_id');
    }

    public function awardQuotation(): HasOne
    {
        return $this->hasOne(ProjectAwarded::class,'quotation_id');
    }

}
