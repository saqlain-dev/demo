<?php

namespace App\Models\HR\Leaves;

use App\Models\Admin\FinancialYear;
use App\Models\Employee;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveAddDeduct extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];


    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'leave_type_id');
    }
    public function employeeDetail(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'EmployeeID')->select(['id','name','employee_no','department_id','designation_id']);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class,'FYID');
    }
}
