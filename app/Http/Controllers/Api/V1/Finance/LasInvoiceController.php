<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Donar\DonarProfile;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\CustomerHub;
use App\Models\Finance\LasInvoice;
use App\Models\Finance\LasInvoiceDetail;
use App\Models\Finance\TaxRate;
use App\Models\Finance\Voucher\Voucher;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LasInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = LasInvoice::query()->with(['BankId.lasConfiguration','customerable','created_by','updated_by','InvoiceDetail'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'date' => 'required',
            'bank_id' => 'nullable',
            'customerable_id' => 'required|integer',
            'customerable_type' => 'required',
//            'invoice_details' => 'array', // Validate as an array
//            'invoice_details.*.item' => 'string|nullable',
//            'invoice_details.*.description' => 'string|nullable',
//            'invoice_details.*.qty' => 'integer|nullable',
//            'invoice_details.*.unit' => 'integer|nullable',
//            'invoice_details.*.amount' => 'float|nullable',
        ]);

        $lastInvoiceNo = DB::table('las_invoices')
            ->select('inv_no')
            ->where('inv_no', 'LIKE', 'LAS-%')
            ->orderBy('inv_no', 'desc')
            ->first();
        if ($lastInvoiceNo) {
            // Extract the numeric part from 'LAS-0001'
            $lastNumber = intval(str_replace('LAS-', '', $lastInvoiceNo->inv_no));

            // Increment the number by 1
            $newNumber = $lastNumber + 1;

            // Format the new employee number (e.g., LAS-0002)
            $newInvNo = 'LAS-' . sprintf('%04d', $newNumber);
        } else {
            // If there are no employees yet, start with 'LAS-0001'
            $newInvNo = 'LAS-0001';
        }
        //$this->input['inv_no'] = $newInvNo;
        $this->input['inv_no'] = $request->input('inv_no') ?? $newInvNo;

        if($request->hasFile('attachment')) {
            $responce = $this->saveImage($request, 'las_invoice');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }

        try {
            DB::beginTransaction();
            $this->input['customerable_type']= ($request->customerable_type == 1) ? DonarProfile::class :  ProjectImplementingPartner::class;
            //dd($this->input);
            $item = LasInvoice::query()->create($this->input);
//            if ($item){
//                if ($request->has('invoice_details')) {
//                    foreach ($request->invoice_details as $detail) {
//                        $detail['las_invoice_id'] = $item->id; // Set the foreign key
//                        LasInvoiceDetail::query()->create($detail); // Create child record
//                    }
//                }
//            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

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
    public function show(LasInvoice $lasInvoice): JsonResponse
    {
        $data['las_invoice'] = $lasInvoice = $lasInvoice->load(['BankId.lasConfiguration','customerable','created_by','updated_by','InvoiceDetail.UnitType']);

        $jvVoucher = Voucher::whereHas('voucherDetail', function ($query) use ($lasInvoice) {
            $query->where('VoucherFrom', 'LAS-INV')
                ->where('VoucherFromID', $lasInvoice->id);
        })->with('voucherDetail')->first();

        $opposite_vouchers = collect(); // initialize empty collection

        if ($jvVoucher) {
            $opposite_vouchers = Voucher::whereHas('voucherDetail', function ($query) use ($jvVoucher) {
                $query->where('VoucherFrom', 'JV')
                    ->where('VoucherFromID', $jvVoucher->id);
            })->with('voucherDetail')->get();
        }

        $data['voucher'] = collect([$jvVoucher])->filter()->merge($opposite_vouchers);

        $data['approval_request']=getNextApproval(57,auth()->user()->designation_id,$lasInvoice->id);
        $data['approval_request_status']=checkApprovalRequestStatus(57,$lasInvoice->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LasInvoice $lasInvoice)
    {
        $request->validate([
            'name' => 'required',
            'date' => 'required',
            'bank_id' => 'required',
            'customerable_id' => 'required|integer',
            'customerable_type' => 'required|string',
//            'invoice_details' => 'array', // Validate as an array
//            'invoice_details.*.id' => 'integer|nullable', // For updating existing details
//            'invoice_details.*.item' => 'string|nullable',
//            'invoice_details.*.description' => 'string|nullable',
//            'invoice_details.*.qty' => 'integer|nullable',
//            'invoice_details.*.unit' => 'integer|nullable',
//            'invoice_details.*.amount' => 'float|nullable',
        ]);
        try {
            DB::beginTransaction();
            $this->input['customerable_type']= $request->customerable_type === 1 ? DonarProfile::class : ProjectImplementingPartner::class;
            LasInvoice::query()->where('id',$lasInvoice->id)->update($this->input);
//            if ($request->has('invoice_details')) {
//                foreach ($request->invoice_details as $detail) {
//                    if (isset($detail['id'])) {
//                        // Update existing record
//                        LasInvoiceDetail::query()->where('id', $detail['id'])->update($detail);
//                    } else {
//                        // Create new record
//                        $detail['las_invoice_id'] = $lasInvoice->id; // Set foreign key
//                        LasInvoiceDetail::query()->create($detail);
//                    }
//                }
//            }
            $lasInvoice->refresh();
            DB::commit();
            return resp(1, 'Successful!', $lasInvoice, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LasInvoice $lasInvoice): JsonResponse
    {
        $lasInvoice->InvoiceDetail()->delete();
        $item = $lasInvoice->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendLasInvoiceForApproval(LasInvoice $item)
    {

        $item = LasInvoice::withSum('InvoiceDetail', 'amount')->find($item->id);
        $claimAmount = $item->invoice_detail_sum_amount ?? 0;

        $approval_process_name=ApprovalProcessName::query()->where('id',57)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',57)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',57)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $claimAmount ){
                        $insert['approval_status']=0;
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('approval_status'=>2);
            LasInvoice::query()->where('id',$item->id)->update($update);
            return resp(1,'Las invoice send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Las invoice approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
