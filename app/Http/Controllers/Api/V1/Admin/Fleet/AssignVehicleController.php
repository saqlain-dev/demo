<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use Illuminate\Http\Request;
use App\Models\Admin\Fleet\Vehicle;
use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\AssignVehicle;
use App\Models\Employee;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class AssignVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'assign_vehicle_view'
        ]);

        $data['assignedVehicle'] = AssignVehicle::with(['DriverId','VehicleId.VehicleType'])->whereNull('deleted_at')->get();
        $data['unAssignedVehicle'] =  Vehicle::whereDoesntHave('assignments')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'assign_vehicle_create'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->validate([
                'driver_id' => 'required',
                'vehicle_id' => 'required',
                'assigned_date' => 'required',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            ]);

            if($request->file('attachment')){
                $response=$this->saveAttachment($request,'vhehicle_attachments');
                $data['attachment'] = $response;
            }
            $item = AssignVehicle::query()->create($data);
            DB::commit();
            return resp('1', 'Assigned Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to assign vehicle. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $this->authorizeAny([
            'assign_vehicle_view'
        ]);

        // $assignedVehicle = AssignVehicle::query()->findOrFail($id)->with('VehicleId', 'VehicleId.vehicleRecords', 'DriverId')->get();
        $assignedVehicle = AssignVehicle::with('VehicleId', 'VehicleId.vehicleRecords', 'DriverId')->findOrFail($id);
        return resp('1', 'Successful!', $assignedVehicle, Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'assign_vehicle_update'
        ]);

        try {
            DB::beginTransaction();
            $data = $request->validate([
                'driver_id' => 'required',
                'vehicle_id' => 'required',
                'assigned_date' => 'required',
                'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
                'returned_date' => 'nullable|date',
            ]);
            $assignedVehicle = AssignVehicle::query()->findOrFail($id);
            if($request->file('attachment')){
                $response=$this->saveAttachment($request,'vhehicle_attachments');
                $data['attachment'] = $response;
            }else {
                unset($data['attachment']); // Just to be extra safe
            }
            $item = $assignedVehicle->update($data);
            DB::commit();
            return resp('1', 'Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update vehicle. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'assign_vehicle_delete'
        ]);
        $assignedVehicle = AssignVehicle::query()->findOrFail($id);
        $item = $assignedVehicle->delete();
        return resp('1', 'Unassigned Successfully!', $item, Response::HTTP_OK);
    }

    public function saveAttachment($request, $folder)
    {

        $file = $request->file('attachment');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    public function getUnAssignedVehicleDropDown(){
        $data['vehicle_type']= Type::getTypeValues('vehicle-type');
        $data['report_type']= Type::getTypeValues('report-type');
        $data['fuel_card']= Type::getTypeValues('fuel-card');
        //$data['chauffeurs']= Employee::query()->where(['designation_id'=>13, 'employee_type'=>13])->get();
        //$data['chauffeurs']= Employee::query()->where(['designation_id'=>28, 'employee_type'=>13])->get();
        $data['chauffeurs'] = Employee::with(['latestAssignedVehicle.VehicleId.VehicleType','latestAssignedVehicle.VehicleId.latestVehicleLog'])
            ->whereIn('designation_id', [116, 28])
            ->where('employee_type', 13)
            ->get();
        $data['employees'] = Employee::with(['designation', 'department'])
                            ->whereNotIn('employee_type', [14, 16, 17, 18])
                            ->get();
        $data['vehicles'] = Vehicle::query()
                                    ->with('VehicleType', 'latestVehicleLog')
                                    ->whereDoesntHave('assignments', function ($query) {
                                        $query->whereNull('returned_date');
                                    })
                                    ->get();
        $data['visit_type']= Type::getTypeValues('visit-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
