<?php

namespace App\Models\Admin\Fleet;

use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogBook extends Model
{
    use SoftDeletes, HasFactory, LogEvents;
    protected $guarded = ['id'];

    public function DriverId():BelongsTo
    {
        return $this->belongsTo(Employee::class,'driver_id');
    }
    public function VisitType():BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'visit_type')->select('id','name');
    }
    public function VehicleId():BelongsTo
    {
        return $this->belongsTo(Vehicle::class,'vehicle_id')->select('id','vehicle_make','registration_no');
    }

    public function VehicleType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'vehicle_type','id')->select(['id','name']);
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
