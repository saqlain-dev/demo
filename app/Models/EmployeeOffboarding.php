<?php

namespace App\Models;

use App\Models\Admin\Library\BookIssued;
use App\Models\Configuration\ClearanceExpCertificate;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeOffboarding extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function EmployeeId():BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->BelongsTo(User::class,'updated_by')->select(['id','name']);
    }

    public function interview(): HasMany
    {
        return $this->hasMany(ExitEmployeeInterview::class,'employee_offboarding_id');

    }
    public function Certificate(): HasMany
    {
        return $this->hasMany(ClearanceExpCertificate::class,'employee_offboarding_id');

    }

    public function bookIssued(): HasMany
    {
        return $this->hasMany(BookIssued::class, 'employee_id')->where('status', 1);
    }
}
