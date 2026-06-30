<?php

namespace App\Http\Controllers\Api\V1\HR\Payscale;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\HR\Payscale\Payscale;
use App\Models\HR\Payscale\PayscaleGrading;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PayscaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'payscale_view',
            'manage_audit_payscale',
        ]);

        $payscale_listing = Payscale::with(['Grading','Level','created_by','updated_by','SalaryRange'])->get();
        foreach($payscale_listing as $key => $payscale){
            $payscale_listing[$key]->positions=Designation::query()->whereIn('id',explode(',',$payscale['position']))->get();
        }
        $data['payscale_listing']=$payscale_listing;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'payscale_create',
        ]);

        $request->validate([
            'grading' => 'required',
            'level' => 'required',
            'position' => 'required|array',
        ]);
        try {
            DB::beginTransaction();
            $checkAlreadyExist=Payscale::query()->where('grading',$request->grading)->where('level',$request->level)->count();
           if($checkAlreadyExist == 0){
               $this->input['position'] = implode(',', $request->position);
               $item = Payscale::query()->create($this->input);
               DB::commit();
               return resp(1, 'Record Created Successfully!', $item, Response::HTTP_CREATED);
           }else{
               return resp(0, 'Payscale already added.', [], Response::HTTP_EXPECTATION_FAILED);
           }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Payscale $payscale): JsonResponse
    {
        $this->authorizeAny([
            'payscale_view',
            'manage_audit_payscale',
        ]);

        $payscale = $payscale->load(['Grading','Level','created_by','updated_by','SalaryRange']);
        foreach ($payscale->SalaryRange as $salaryRange) {

            $approval_request=getNextApproval(40,auth()->user()->designation_id,$salaryRange->id);
            $approval_request_status=checkApprovalRequestStatus(40,$salaryRange->id);

            $salaryRange->approval_request = $approval_request;
            $salaryRange->approval_request_status = $approval_request_status;
        }
        $payscale['positions']=Designation::query()->whereIn('id',explode(',',$payscale->position))->get();
        return resp('1', 'Successful!', $payscale, Response::HTTP_OK);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payscale $payscale)
    {
        $this->authorizeAny([
            'payscale_update',
        ]);

        $request->validate([
            'grading' => 'required',
            'level' => 'required',
            'position' => 'required|array',
        ]);
        try {
            DB::beginTransaction();
            $checkAlreadyExist=Payscale::query()->where('grading',$request->grading)->where('level',$request->level)->where('id', '!=', $payscale->id)->count();
            if($checkAlreadyExist == 0) {
                $this->input['position'] = implode(',', $request->position);
                $item = $payscale->update($this->input);
                DB::commit();
            }else{
                return resp(0, 'Payscale already added.', [], Response::HTTP_EXPECTATION_FAILED);
            }
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payscale $payscale): JsonResponse
    {
        $this->authorizeAny([
            'payscale_delete',
        ]);

        $item = $payscale->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function payScaleDropdown(){
        //$data['grading']= Type::getTypeValues('grading');
        $data['grading']= PayscaleGrading::all();
        $data['level']= Type::getTypeValues('level');
        $data['position']= Designation::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
