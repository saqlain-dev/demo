<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\HR\Insurance\EmployeeRelative;
use App\Models\HR\Policy;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'policy_view',
            'governance_policy_view',
            'manage_employee_portal',
        ]);

        $data = Policy::query()->with(['policyType','createdBy'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'policy_create',
            'governance_policy_create',
        ]);

        $request->validate([
            'policy_type_id' => 'required|integer',
            'description' => 'required|string',
            'attachment' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $responce = $this->saveAttachment($request, 'policy');
                if ($responce) {
                    $attachmentPath = $responce;
                }
            }

//            $extension = $request->file('attachment')->getClientOriginalExtension();
//            $attachmentPath = $request->file('attachment')->storeAs('images/policy', time() . '_attachment.' . $extension, 'public');

            $item = Policy::create([
                'policy_type_id' => $request->policy_type_id,
                'description' => $request->description,
                'attachment' => $attachmentPath,
            ]);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($attachmentPath)) {
                Storage::delete($attachmentPath);
            }
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachment($request,$folder){

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
     * Display the specified resource.
     */
    public function show(Policy $policy)
    {
        $this->authorizeAny([
            'policy_view',
            'governance_policy_view',
            'manage_employee_portal',
        ]);

        $data['policy']=$policy->load('policyType','createdBy');
        $data['approval_request']=getNextApproval(23,auth()->user()->designation_id,$policy->id);
        $data['approval_request_status']=checkApprovalRequestStatus(23,$policy->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Policy $policy)
    {
        $this->authorizeAny([
            'policy_update',
            'governance_policy_update',
        ]);

        $request->validate([
            'policy_type_id' => 'required|integer',
            'description' => 'required|string',
        ]);

        if($request->hasFile('attachment')) {
            $responce = $this->saveAttachment($request, 'policy');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }

        try {
            DB::beginTransaction();

            $item = $policy->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'policy_id' => 'required',
            'attachment' => 'required|file',
        ]);
        $policy = Policy::query()->findOrFail($request->policy_id);
        //$attachmentPath = $policy->attachment;
        if($request->hasFile('attachment')) {
            $responce = $this->saveAttachment($request, 'policy');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
//        if ($request->hasFile('attachment')){
//            Storage::disk('public')->delete($attachmentPath);
//            $extension = $request->file('attachment')->getClientOriginalExtension();
//            $attachmentPath = $request->file('attachment')->storeAs('images/policy', time() . '_attachment.' . $extension, 'public');
//        }

        $item = $policy->update([
            'attachment' => $this->input['attachment'],
        ]);

        return resp(1, 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Policy $policy)
    {
        $this->authorizeAny([
            'policy_delete',
            'governance_policy_delete',
        ]);

        $policy->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['policies_types'] = Type::getTypeValues('policies-types');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendPolicyForApproval(Policy $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',23)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',23)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            Policy::query()->where('id',$item->id)->update($update);
            return resp(1,'Policy send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
