<?php

namespace App\Models\HR\Attendance;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeManuelAttendance extends Model
{
    use HasFactory,LogEvents;
    protected $guarded=['id'];

    public function Userid():BelongsTo
    {
        return $this->belongsTo(Employee::class,'userid','id');
    }

    public function manualAttendance(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'manual_attendance_type');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
