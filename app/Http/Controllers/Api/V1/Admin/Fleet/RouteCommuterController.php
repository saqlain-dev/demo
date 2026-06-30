<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\RouteCommuter;
use App\Models\Admin\Fleet\RouteManagement;
use App\Models\Admin\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RouteCommuterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = RouteCommuter::with(['EmployeeId'=>['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'],'created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'route_management_id' => 'required',
            'employee_id' => 'required',
            'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = RouteCommuter::query()->create($this->input);
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
    public function show(RouteCommuter $routeCommuter): JsonResponse
    {
        $routeCommuter = $routeCommuter->load(['EmployeeId','created_by','updated_by']);
        return resp('1', 'Successful!', $routeCommuter, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RouteCommuter $routeCommuter)
    {
        $request->validate([
            'route_management_id' => 'required',
            'employee_id' => 'required',
            'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $routeCommuter->update($this->input);
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
    public function destroy(RouteCommuter $routeCommuter): JsonResponse
    {
        $item = $routeCommuter->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
