<?php

namespace App\Http\Controllers\Api\V1\HR\Leaves;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Employee;
use App\Models\HR\Leaves\LeaveBalance;
use App\Models\HR\Leaves\LeaveBalanceDetail;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'leave_entitlement_view',
        ]);

        $data['leave_balance']=LeaveBalanceDetail::query()->with('financialYearDetail.financialYear','leaveType')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'leave_entitlement_create',
        ]);

        ini_set('max_execution_time', 0); // 120 seconds
        $request->validate([
            'LeaveTypeID' => 'required|integer',
            'LeaveBalance' => 'required|integer',
            'FYID' => 'required|integer',

        ]);
        try {

            $checkLeaveBalance=LeaveBalanceDetail::query()->where('LeaveTypeID',$request->LeaveTypeID)->where('FYID',$request->FYID)->count();
            if($checkLeaveBalance == 0){
                DB::beginTransaction();
                DB::statement('EXEC FYLeaveBalance ?, ?,?', [$request->LeaveTypeID, $request->LeaveBalance,$request->FYID]);
                /*$LeaveBalanceDetail=array(
                    'LeaveTypeID'=>$request->LeaveTypeID,
                    'LeaveBalance'=>$request->LeaveBalance,
                    'FYID'=>$request->FYID,
                    'EntryDate'=>date('Y-m-d H:i:s')
                );

                $leaveBalance=LeaveBalanceDetail::query()->create($LeaveBalanceDetail);
                if($leaveBalance){
                    $employees=Employee::query()->where('employee_type',13)->get();
                    foreach($employees as $emp){
                        $balance=array(
                            'EmployeeID'=>$emp['id'],
                            'LeaveTypeID'=>$request->LeaveTypeID,
                            'Balance'=>$request->LeaveBalance,
                            'FYID'=>$request->FYID,
                        );
                        LeaveBalance::query()->create($balance);
                    }
                }*/

                DB::commit();
                return resp(1, 'Successful!', [], Response::HTTP_CREATED);
            }else{
                return resp(0, 'Leave Balance for this financial year is already inserted.', Response::HTTP_EXPECTATION_FAILED);
            }

        }catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveBalance $leaveBalance)
    {
        $this->authorizeAny([
            'leave_entitlement_view',
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveBalance $leaveBalance)
    {
        $this->authorizeAny([
            'leave_entitlement_update',
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveBalance $leaveBalance)
    {
        $this->authorizeAny([
            'leave_entitlement_delete',
        ]);
//        $item  = $leaveBalance->delete();
//        return resp('1', 'Leave deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function leaveBalanceDropDown()
    {
        $data['financial_year']=FinancialYear::query()->where('status',1)->first();
        $data['leave_type']=Type::getTypeValues('leave-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
