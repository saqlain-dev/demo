<?php

namespace App\Models\HR\Recruitment;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewResult extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];
    
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }    
    public function manageJob(): BelongsTo
    {
        return $this->belongsTo(ManageJob::class, 'manage_job_id');
    }    
    public function applyJob(): BelongsTo
    {
        return $this->belongsTo(ApplyJob::class, 'apply_job_id');
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
