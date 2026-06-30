<?php

namespace App\Models\HR\Payroll;

use App\Models\Program\Project\ProjectProfile;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSalarySegregation extends Model
{
    use SoftDeletes,LogEvents;
    protected $guarded=['id'];

    public function projectDetail(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }
}
