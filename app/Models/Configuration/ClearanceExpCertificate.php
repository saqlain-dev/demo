<?php

namespace App\Models\Configuration;

use App\Models\EmployeeOffboarding;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClearanceExpCertificate extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function EmployeeOffboardingId(): BelongsTo
    {
        return $this->belongsTo(EmployeeOffboarding::class,'employee_offboarding_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->BelongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function certificateType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'certificate_type');
    }
}
