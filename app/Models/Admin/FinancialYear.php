<?php

namespace App\Models\Admin;

use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialYear extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'end_date' => 'date'
    ];

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'financial_year');
    }
}
