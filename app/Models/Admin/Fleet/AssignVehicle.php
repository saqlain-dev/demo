<?php

namespace App\Models\Admin\Fleet;

use App\Models\Employee;
use App\Traits\LogEvents;
use App\Models\Admin\Fleet\Vehicle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignVehicle extends Model
{
    use SoftDeletes, HasFactory, LogEvents;

    protected $guarded = ['id'];

    public function VehicleId(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }


    public function DriverId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'driver_id');
    }


}
