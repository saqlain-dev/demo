<?php

namespace App\Models\Admin\Fleet;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RouteCommuter extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function EmployeeId():BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
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
