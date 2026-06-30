<?php

namespace App\Http\Controllers\Api\V1\Division;

use App\Http\Controllers\Controller;
use App\Models\Division\Division;
use App\Models\Division\DivisionEmployee;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['division_list']=Division::query()->with('divisionHead')->get();
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
            'division_head_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $division=Division::query()->create($this->input);

            if($division){
                $input=[
                    'division_id'=>$division->id,
                    'employee_id'=>$request->division_head_id,
                ];
                DivisionEmployee::query()->create($input);
            }
            DB::commit();
            return resp(1, 'Successful!', $division, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Division $division)
    {

        $data['division']=$division->load(['divisionHead','divisionEmployee.employee','divisionEmployee.employee.department','divisionEmployee.employee.designation','divisionEmployee.employee.salutation']);
        $data['employee_listing']=Employee::query()->with('department','designation','salutation')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Division $division)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required',
            'division_head_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $division->update($this->input);
            $division->refresh();

            if($division){
               $divisionEmployee= DivisionEmployee::query()->where('division_id',$division->id)->where('employee_id',$request->division_head_id)->first();
               if(empty($divisionEmployee)){
                   $input=[
                       'division_id'=>$division->id,
                       'employee_id'=>$request->division_head_id,
                   ];
                   DivisionEmployee::query()->create($input);
               }


            }
            DB::commit();
            return resp(1, 'Successful!', $division, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Division $division)
    {
        $division->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function divisionDropDown()
    {
        $data['employees_list']=Employee::query()->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addDivisionEmployee(Request $request)
    {
        $request->validate([
            'division_id' => 'required|integer', // Only required for updates
            'employee_id' => 'required|integer',
        ]);


        try {
            DB::beginTransaction();
            DivisionEmployee::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteDivisionEmployee(Request $request)
    {
        $request->validate([
            'division_emp_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $division_emp_id=$request->division_emp_id;
            $division_employee=DivisionEmployee::query()->find($division_emp_id);
            if($division_employee){
                $division_employee->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
