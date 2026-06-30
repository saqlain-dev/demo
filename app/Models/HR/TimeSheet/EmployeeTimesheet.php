<?php

namespace App\Models\HR\TimeSheet;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTimesheet extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function employeeSheetDetail(): HasMany
    {
        return $this->hasMany(EmployeeTimesheetDetail::class,'employee_time_sheet_id');
    }

    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employeeID');
    }
}
