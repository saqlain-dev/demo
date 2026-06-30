<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\FuelRequest;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\ApprovalProcess;
use App\Models\InvoiceFuelRequest;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Procurement;


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
            'fuel_request_view',
            'manage_employee_portal',
        ]); 
        $data['fuelRequest'] = $fuelRequest->load(['ProjectId','VehicleId','created_by','updated_by','FuelConsumption','invoiceFuelRequest'=>['procurement','invoice','procurementDetail.item']]);
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

        $approval_process_name=ApprovalProcessName::query()->where('id',37)->first();
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
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

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
   
    public function storeInvoice(Request $request)
    {
        // dd(\App\Models\Admin\Fleet\FuelRequest::first());
        $data = $request->validate([ 
            'invoice_date' => 'required|date', 
            'invoice_amount' => 'required|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',  
            'fuel_request_id' => 'required|integer|exists:fuel_requests,id',  
            'procurement_id' => 'required|integer|exists:procurements,id',  
            'supplier_id' => 'required|integer|exists:vendors,id',  
            'procurement_detail_id' => 'required|integer|exists:procurement_details,id',   
            'remarks' => 'nullable|string',
            'amount' => 'nullable|numeric', 
            'name' => 'nullable|string', 
        ]);
        DB::beginTransaction();
        try {
            $invoiceFilePath = null; 
            if ($request->hasFile('invoice_file')) {                
                $invoiceFilePath = $this->saveFile($request,'fuel-request-invoice-file');
            }

            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
            // Create Invoice
            $inputDatetime =  $data['invoice_date'];  
            $invoice = Invoice::create([
                'invoice_number' => $inVNO,
                'invoice_date' =>  Carbon::createFromFormat('Y-m-d\TH:i', $inputDatetime)->format('Y-m-d H:i:s'), 
                'invoice_amount' => $data['invoice_amount'],
                'invoice_file' => $invoiceFilePath, 
                'created_by' => Auth::id(),
                'invoice_status' => 4, 
                'supplier_id'=>$data['supplier_id']
            ]);  
            $fuelRequest = InvoiceFuelRequest::create([
                'invoice_id' =>$invoice->id,
                'procurement_id'=>$data['procurement_id'], 
                'fuel_request_id'=>$data['fuel_request_id'],
                'procurement_detail_id'=>$data['procurement_detail_id'], 
                'name'=>$data['name'], 
                'remarks'=>$data['remarks'], 
            ]); 
            DB::commit();

            return resp('1', 'Invoice created successfully!',   $invoice->load(['invoiceFuelRequest'=>['procurementDetail.item','procurement']]), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create invoice', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function saveFile($request,$folder){
        $file = $request->file('invoice_file');
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
    function dropdown(){
        $this->authorizeAny([
            'log_book_view'
        ]);
        $data['vendor_list']= Vendor::get();
        $data['procurement_list']= Procurement::with('items.item')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
