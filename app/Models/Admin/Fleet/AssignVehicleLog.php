<?php
namespace App\Models\Admin\Fleet;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Model; 
use App\Traits\LogEvents;
class AssignVehicleLog extends Model
{
    use LogEvents;
    protected $fillable = [
        'assign_vehicle_id',
        'driver_id',
        'vehicle_id',
        'action',
        'changes',
        'created_by',
        'assigned_date',
    ];
    protected $casts = [
        'changes' => 'array',
    ];
    function assignVehicle() {
        return $this->belongsTo(AssignVehicle::class);
    }
    function performedBy() {
        return $this->belongsTo(User::class);
    }

    function driver() {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    function updatedBy() {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getChangesAttribute($value)
    {
        $data = json_decode($value, true);

        $relations = [
            'assign_vehicle_id' => AssignVehicle::class,
            'driver_id' => Employee::class,
            'vehicle_id' => Vehicle::class,
            'created_by' => User::class,
            'updated_by' => User::class
        ];

        foreach (['old', 'new'] as $type) {
            if (!isset($data[$type])) continue;

            foreach ($relations as $key => $model) {
                if (isset($data[$type][$key])) {
                    $data[$type][$key] = $model::find($data[$type][$key]);
                }
            }
        }

        return $data;
    }
}
