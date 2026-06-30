<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\FuelRequest;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FuelRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'fuel_request_view'
        ]);

        $data = FuelRequest::with('ProjectId','VehicleId','created_by','updated_by','FuelConsumption')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'fuel_request_create'
        ]);

        $request->validate([
            'project_id' => 'required',
            //'vehicle_id' => 'required',
            'date_of_request' => 'required',
            'vehicle_pool_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = FuelRequest::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelRequest $fuelRequest): JsonResponse
    {
        $this->authorizeAny([
            'fuel_request_view'
        ]);

        $data['fuelRequest'] = $fuelRequest->load('ProjectId','VehicleId','created_by','updated_by','FuelConsumption');
        $data['approval_request']=getNextApproval(37,auth()->user()->designation_id,$fuelRequest->id);
        $data['approval_request_status']=checkApprovalRequestStatus(37,$fuelRequest->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelRequest $fuelRequest)
    {
        $this->authorizeAny([
            'fuel_request_update'
        ]);

        $request->validate([
            'project_id' => 'required',
            //'vehicle_id' => 'required',
            'date_of_request' => 'required',
            'vehicle_pool_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $fuelRequest->update($this->input);
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
    public function destroy(FuelRequest $fuelRequest): JsonResponse
    {
        $this->authorizeAny([
            'fuel_request_delete'
        ]);

        $item = $fuelRequest->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendFuelRequestForApproval(FuelRequest $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',37)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',37)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            FuelRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Fuel request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Fuel request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
