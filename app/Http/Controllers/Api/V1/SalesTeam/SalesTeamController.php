<?php

namespace App\Http\Controllers\Api\V1\SalesTeam;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalesTeam\SalesTeam;
use App\Models\SalesTeam\SalesTeamEmployee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalesTeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['sales_team_list']=SalesTeam::query()->with('salesTeamHead')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required',
            'sales_head_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $salesTeam=SalesTeam::query()->create($this->input);

            if($salesTeam){
                $input=[
                    'sales_team_id'=>$salesTeam->id,
                    'employee_id'=>$request->sales_head_id,
                    'discount'=>$request->discount,
                ];
                SalesTeamEmployee::query()->create($input);
            }
            DB::commit();
            return resp(1, 'Successful!', $salesTeam, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesTeam $sales_team)
    {
        $data['sales_team']=$sales_team->load(['salesTeamHead','salesTeamEmployee.employee','salesTeamEmployee.employee.department','salesTeamEmployee.employee.designation','salesTeamEmployee.employee.salutation']);
        $data['employee_listing']=Employee::query()->with('department','designation','salutation')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesTeam $sales_team)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required',
            'sales_head_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $sales_team->update($this->input);
            $sales_team->refresh();

            if($sales_team){
                $salesTeamEmployee= SalesTeamEmployee::query()->where('sales_team_id',$sales_team->id)->where('employee_id',$request->sales_head_id)->first();
                if(empty($salesTeamEmployee)){
                    $input=[
                        'sales_team_id'=>$sales_team->id,
                        'employee_id'=>$request->sales_head_id,
                        'discount'=>$request->discount,
                    ];
                    SalesTeamEmployee::query()->create($input);
                }


            }
            DB::commit();
            return resp(1, 'Successful!', $sales_team, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesTeam $sales_team)
    {
        $sales_team->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function addSalesTeamEmployee(Request $request)
    {
        $request->validate([
            'sales_team_id' => 'required|integer', // Only required for updates
            'employee_id' => 'required|integer',
        ]);


        try {
            DB::beginTransaction();
            SalesTeamEmployee::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteSalesTeamEmployee(Request $request)
    {
        $request->validate([
            'sale_team_emp_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $sale_team_emp_id=$request->sale_team_emp_id;
            $sale_team_employee=SalesTeamEmployee::query()->find($sale_team_emp_id);
            if($sale_team_employee){
                $sale_team_employee->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
