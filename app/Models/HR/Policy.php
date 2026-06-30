<?php

namespace App\Models\HR;

use App\Models\HR\Insurance\EmployeeInsurances;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function policyType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'policy_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
}
