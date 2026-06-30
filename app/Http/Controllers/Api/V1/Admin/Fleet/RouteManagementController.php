<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\Fleet\RouteManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RouteManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'route_management_view'
        ]);

        $data = RouteManagement::with(['VehicleId','DriverId','created_by','updated_by','Commuters.EmployeeId'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'route_management_create'
        ]);

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'route_name' => 'required',
            'start' => 'required',
            'end' => 'required',
            'pick_up_time' => 'required',
            'drop_off_time' => 'required',
            'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = RouteManagement::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RouteManagement $routeManagement): JsonResponse
    {
        $this->authorizeAny([
            'route_management_view'
        ]);

        $routeManagement = $routeManagement->load(['VehicleId','DriverId','created_by','updated_by','Commuters.EmployeeId'=>['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo']]);
        return resp('1', 'Successful!', $routeManagement, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RouteManagement $routeManagement)
    {
        $this->authorizeAny([
            'route_management_update'
        ]);

        $request->validate([
            'vehicle_id' => 'required',
            'driver_id' => 'required',
            'route_name' => 'required',
            'start' => 'required',
            'end' => 'required',
            'pick_up_time' => 'required',
            'drop_off_time' => 'required',
            'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $routeManagement->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RouteManagement $routeManagement): JsonResponse
    {
        $this->authorizeAny([
            'route_management_delete'
        ]);

        $item = $routeManagement->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
