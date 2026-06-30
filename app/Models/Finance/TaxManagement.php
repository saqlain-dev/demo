<?php

namespace App\Models\Finance;

use App\Models;
use App\Models\User;
use App\Models\Country;
use App\Models\Province;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxManagement extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function TaxType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'tax_type');
    }

    public function TaxComputation(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'tax_computation');
    }

    public function CountryId(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
    public function taxScope(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_scope');
    }
    public function taxGroup(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'tax_group');
    }
    public function ProvinceId()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
    public function CoaId()
    {
        return $this->belongsTo(ChartOfAccount::class, 'coa_id');
    }
}
