<?php

namespace App\Models\HR\Complaint;

use App\Models\ComplaintCommittee;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department');
    }

    public function natureOfComplaint(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'nature_of_complaint');
    }

    public function complainFrom(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'complain_from_emp');
    }
    public function complainAgainst(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'complain_against_emp');
    }
    public function complainAgainstDepartment(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'complain_against_emp');
    }

    public function getComplainAgainstOrDepartmentAttribute()
    {
        if ($this->complain_type == 0) {
            return $this->complainAgainst;
        } elseif ($this->complain_type == 1) {
            return $this->complainAgainstDepartment;
        }

        return null;
    }

    public function committeeMembers(): HasMany
    {
        return $this->HasMany(ComplaintCommittee::class,'complaint_id');
    }
}
