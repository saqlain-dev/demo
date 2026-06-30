<?php

namespace App\Models\Configuration;

use App\Models\Employee;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeChangeLog extends Model
{
    use LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function employeeDetail():BelongsTo
    {
        return $this->belongsTo(Employee::class,'EmployeeID');
    }
}
