<?php

namespace App\Http\Controllers\Api\V1\HR\Leaves;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Employee;
use App\Models\HR\Leaves\EmployeeLeave;
use App\Models\HR\Leaves\LeaveAddDeduct;
use App\Models\HR\Leaves\LeaveBalance;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LeaveAddDeductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'partial_leave_view',
        ]);
        
        $data['addDeductLeaves']=LeaveAddDeduct::query()->with('leaveType','employeeDetail','financialYear.financialYear')->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'partial_leave_create',
        ]);
        
        try {
            DB::beginTransaction();

            $request->validate([
                'FYID' => 'required',
                'type' => 'required',
                'leave_type_id' => 'required',
                'EmployeeID' => 'required|integer',
                'NoOfDays' => 'required|integer|gt:0',
            ]);
            $FYID=$request->FYID;
            $leave=LeaveAddDeduct::query()->create($this->input);
            if($leave){
                $empBalance=LeaveBalance::query()->where('FYID',$request->FYID)->where('EmployeeID',$request->EmployeeID)->where('LeaveTypeID',$request->leave_type_id)->first();

                if($leave->type == 1){
                    $empBalance->Balance+=$request->NoOfDays;

                }else{
                    $empBalance->Balance-=$request->NoOfDays;
                    $empBalance->Availed+=$request->NoOfDays;

                }
                $empBalance->save();
            }
            DB::commit();
            return resp('1', 'Record added Successfully!', $leave->load('leaveType','employeeDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveAddDeduct $leaveAddDeduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveAddDeduct $leaveAddDeduct)
    {
        $this->authorizeAny([
            'partial_leave_update',
        ]);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveAddDeduct $leaveAddDeduct)
    {
        $this->authorizeAny([
            'partial_leave_delete',
        ]);
        
        //
    }

    public function addDeductDropDown()
    {
        $data['leave_types']=Type::getTypeValues('leave-type');
        $data['employeeDetail']=Employee::query()->where('id',auth()->user()->employee_id)->with(['designation','employeeTyp','gender'])->first();
        $data['allEmployeeList']=Employee::query()->with('designation')->get();
        $data['financial_year']=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
}
