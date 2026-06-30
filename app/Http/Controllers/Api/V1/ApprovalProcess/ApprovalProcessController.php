<?php

namespace App\Http\Controllers\Api\V1\ApprovalProcess;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessName;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Constraint\Count;
use Termwind\Components\Dd;

class ApprovalProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-program',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
        ]);

        $data['designation']=Designation::with('users')->get();
        $data['approval_process_list']=ApprovalProcessName::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function addProcess($id){
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
        ]);

        $data['process_detail']=ApprovalProcess::with('processName')->where('approval_process_id',$id)->get();
        $data['designation']=Designation::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-program',
            'manage_finance_configuration',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
        ]);

        $request->validate([
            'approval_process_id' => 'required',
        ]);
        $Tcount=count($request->approval);
        $lastIndex=last($request->approval);
        if($Tcount == $lastIndex['process_order']){

        $approvalProcessId=ApprovalProcessName::query()->findOrFail($request->approval_process_id);

        $approvalProcessId->approvalProcess()->delete();
        $approvalProcessId->approvalProcess()->createMany($request->approval);

        return resp(1,'Successful!', $approvalProcessId,Response::HTTP_CREATED);
        }else{
            return resp(0,'Approval Sequence is incorrect.', [],Response::HTTP_CREATED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ApprovalProcess $approvalProcess)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-program',
            'configuration-admin',
            'configuration-hr',
            'manage_communication_configuration',
        ]);

        $data['approval_process_names_list']=ApprovalProcessName::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ApprovalProcess $approvalProcess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApprovalProcess $approvalProcess)
    {
        //
    }
}
