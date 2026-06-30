<?php

namespace App\Observers; 
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Fleet\AssignVehicle; 
use App\Models\Admin\Fleet\AssignVehicleLog; 

class AssignVehicleObserver
{
    public function created(AssignVehicle $assignVehicle)
    {
        $this->logChange($assignVehicle, 'created');
    }

    public function updated(AssignVehicle $assignVehicle)
    {
        $changes = [
            'old' => $assignVehicle->getOriginal(),
            // 'new' => $assignVehicle->getChanges(),
            'new' => $assignVehicle->fresh()->toArray(),
        ];

        $this->logChange($assignVehicle, 'updated', $changes);
    }

    public function deleted(AssignVehicle $assignVehicle)
    {
        $this->logChange($assignVehicle, 'deleted');
    }

    protected function logChange(AssignVehicle $assignVehicle, $action, $changes = null)
    {
        AssignVehicleLog::create([
            'assign_vehicle_id' => $assignVehicle->id,
            'driver_id' => $assignVehicle->driver_id,
            'vehicle_id' => $assignVehicle->vehicle_id,
            'assigned_date'=>$assignVehicle->assigned_date,
            'returned_date' => $assignVehicle->returned_date,
            'action' => $action,
            'changes' => $changes,
            'performed_by' => $assignVehicle->created_by,
        ]);
    }
}
