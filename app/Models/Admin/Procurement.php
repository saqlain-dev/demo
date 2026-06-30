<?php

namespace App\Models\Admin;

use App\Models\Finance\Budget\ProjectBudget;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Procurement extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'districts' => 'array'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ProcurementDetail::class);
    }

    public function qualificationType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'qualification_type_id')->select('id','name');
    }

    public function procurementMethod(): BelongsTo
    {
        return $this->belongsTo(RfqType::class, 'procurement_method');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(ProjectBudget::class,'program_budget_id');
    }

}
