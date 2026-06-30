<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\Library\BookIssued;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\EmployeeOffboarding;
use App\Models\HR\TimeSheet\EmployeeTimesheet;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeOffboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_off_boarding_view',
            'manage_audit_employee_management',
        ]);

        $item=EmployeeOffboarding::query()->with(['interview','Certificate.certificateType','EmployeeId' => ['headOffice','branchOffice','designation','marital','department','gender','report','reportTo'],'createdBy','updatedBy'])->get();
        return resp(1,'Successful!', $item,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('employee_off_boarding_create');

        $request->validate([
            'employee_id' => 'required',
            'resignation_date' => 'required',
            'reason' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'EmployeeResignationLetter');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = EmployeeOffboarding::query()->create($this->input);
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
    public function show(EmployeeOffboarding $employee_off_boarding): JsonResponse
    {
        $this->authorizeAny([
            'employee_off_boarding_view',
            'manage_audit_employee_management',
        ]);

        $data['employee_off_board'] = $employee_off_boarding->load(['interview','Certificate.certificateType','EmployeeId' => ['headOffice','branchOffice','designation','marital','department','gender','report','reportTo','employeeInventory'],'createdBy','updatedBy']);
        $data['issuedBooks'] = BookIssued::where('employee_id', $employee_off_boarding->employee_id)
            ->where('status', 1)
            ->get();
        $data['approval_request']=getNextApproval(49,auth()->user()->designation_id,$employee_off_boarding->id);
        $data['approval_request_status']=checkApprovalRequestStatus(49,$employee_off_boarding->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeOffboarding $employee_off_boarding)
    {
        $this->authorize('employee_off_boarding_update');

        $request->validate([
            'employee_id' => 'required',
            'resignation_date' => 'required',
            'reason' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'EmployeeResignationLetter');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $employee_off_boarding->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImages($request,$folder){

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeOffboarding $employee_off_boarding): JsonResponse
    {
        $this->authorize('employee_off_boarding_delete');

        $item = $employee_off_boarding->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendEmployeeOffBoardingForApproval(EmployeeOffboarding $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',49)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',49)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            EmployeeOffboarding::query()->where('id',$item->id)->update($update);
            return resp(1,'Employee Off Boarding send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Employee Off Boarding approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
