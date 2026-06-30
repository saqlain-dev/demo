<?php

namespace App\Models\HR\Leaves;

use App\Models\Admin\FinancialYear;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalanceDetail extends Model
{
    use LogEvents;
    protected $guarded=['id'];

    public function financialYearDetail(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class,'FYID');
    }
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'LeaveTypeID');
    }
}
