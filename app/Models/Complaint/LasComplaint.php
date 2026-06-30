<?php

namespace App\Models\Complaint;

use App\Models\District;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LasComplaint extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function forwardTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'forwarded_to');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'priority');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'gender');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class,'district');
    }

    public function complainantCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'complainant_category');
    }
    public function feedbackCategory(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'feedback_category');
    }
}
