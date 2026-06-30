<?php

namespace App\Models\Finance;

use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasInvoiceDetail extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function UnitType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'unit_type')->select(['id', 'name']);
    }
}
