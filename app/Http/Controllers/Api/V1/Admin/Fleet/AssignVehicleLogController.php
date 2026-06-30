<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Models\Admin\Fleet\AssignVehicleLog;
use Illuminate\Http\Request; 
use Illuminate\Http\Response; 
use App\Http\Controllers\Controller;
class AssignVehicleLogController extends Controller
{

    public function index(Request $request)
    {
        $data = AssignVehicleLog::with(['performedBy'])
            ->when($request->assign_vehicle_id, fn ($q) => $q->where('assign_vehicle_id', $request->assign_vehicle_id))
            ->when($request->driver_id, fn ($q) => $q->where('driver_id', $request->driver_id))
            ->when($request->vehicle_id, fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->get();
            
        return resp('1', 'Assign Vehicle Log  get Successfully!', $data, Response::HTTP_OK); 
    }

}
