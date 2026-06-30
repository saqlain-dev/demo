<?php

namespace App\Models;

use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadQualification extends Model
{
    use LogEvents,SoftDeletes;

    protected $guarded=['id'];

    public function qualificationStatus(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'qualification_status');
    }

    public function qualifiedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'qualified_by');
    }

    public function employeeRef(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function assignTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'assign_to');
    }
}
