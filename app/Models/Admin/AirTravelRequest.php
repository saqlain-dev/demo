<?php

namespace App\Models\Admin;

use App\Models\AtrVendorDocument;
use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirTravelRequest extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function items(): HasMany
    {
        return $this->hasMany(AirTravelRequestDetail::class, 'parent_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'department_id');
    }
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'accommodation_id');
    }
    public function externalVisitor(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'external_visitor_id');
    }
    public function airlineCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'airline_category_id');
    }

    public function airTravelReqVendor(): HasMany
    {
        return $this->hasMany(AtrVendor::class, 'atr_id');
    }

    public function vendorAtrQuotation(): HasMany
    {
        return $this->hasMany(VendorAtrQuotation::class, 'atr_id');
    }

    public function atrInvoice(): HasMany
    {
        return $this->hasMany(AtrVendorDocument::class, 'atr_id');
    }
}
