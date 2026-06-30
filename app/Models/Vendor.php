<?php

namespace App\Models;

use App\Models\Admin\PurchaseRequestRfq;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function Supplier(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'supplier_id');
    }
    public function serviceProvider(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'supplier_id');
    }

    public function rfqs(): BelongsToMany
    {
        return $this->belongsToMany(PurchaseRequestRfq::class, 'pr_rfq_vendors','vendor_id','purchase_request_rfq_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'vendor_id');
    }

    public function vendorUser(): HasOne
    {
        return $this->hasOne(User::class, 'vendor_id', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class,'district_id');
    }

    public function stockPosition(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'stock_position');
    }
    public function companyPosition(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'company_position');
    }

    public function visitingMemberFir(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'visiting_team_member_f');
    }
    public function visitingMemberSec(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'visiting_team_member_s');
    }
}
