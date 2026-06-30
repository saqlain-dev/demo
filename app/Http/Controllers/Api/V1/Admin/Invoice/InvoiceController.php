<?php

namespace App\Http\Controllers\Api\V1\Admin\Invoice;

use App\Http\Controllers\Controller;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\ConsultantContract\ConsultantContractDetail;
use App\Models\Admin\Invoice\InvoiceAudit;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\EmployeePayrollDetail;
use App\Models\EmployeePayrollMaster;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\LasConfiguration;
use App\Models\Finance\SalaryAccountConfiguration;
use App\Models\Finance\SalaryAccountHeadSetting;
use App\Models\GRN;
use App\Models\GrnItem;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\SalaryAllowanceDeduction;
use App\Models\WorkOrder\WorkOrder;
use App\Models\WorkOrder\WorkOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'invoice_view',
            'auction_invoices_view',
            'manage_audit_procurement',
            'manage_vendor_portal',
        ]);

        $data['invoice_list']=Invoice::query()->with('invoiceItems.itemDetail','grn.grnItem','vendorDetail','consultantContract.CcItems','workOrder.WoItems','rfq.disposeRequest','invoiceFuelRequest')->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function vendorInvoices(Request $request)
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);

        $request->validate([
            'vendor_id' => 'required',
        ]);
        $data['invoice_list']=Invoice::query()->where('supplier_id',$request->vendor_id)->with('invoiceItems.itemDetail','grn.grnItem','vendorDetail')->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $this->authorizeAny([
    //         'invoice_create',
    //         'auction_invoices_create',
    //         'manage_payment_requests',
    //         'manage_vendor_portal',
    //     ]);

    //     try {
    //         DB::beginTransaction();
    //         $request->validate([
    //             'invoice_date' => 'required|date',
    //             'invoice_amount' => 'required|numeric',
    //             'grn_id' => 'required|integer',
    //         ]);
    //         $grn=GRN::query()->findOrFail($request->grn_id);
    //         $this->input['project_id'] = GRN::where('id', $request->grn_id)
    //             ->with([
    //                 'poDetails.rfqDetail.purchase_request.project:id','poDetails.tenderDetails.purchaseRequest.project:id' // Only retrieve project_id
    //             ])
    //             ->first()
    //             ->poDetails
    //             ->rfqDetail
    //             ->purchase_request
    //             ->project
    //             ->id;
    //         $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
    //         $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
    //         $this->input['invoice_number']=$inVNO;
    //         $this->input['grn_id']=$request->grn_id;
    //         $this->input['supplier_id']=$grn->vendor_id;
    //         $this->input['invoice_status']=4;
    //         $this->input['invoice_date']=date('Y-m-d H:i:s',strtotime($request->invoice_date));
    //         if($request->hasFile('invoice_file')) {

    //             $responce = $this->saveInvoiceFile($request, 'invoice');

    //             if ($responce) {
    //                 $this->input['invoice_file'] = $responce;
    //             }
    //         }else{
    //             unset($this->input['invoice_file']);
    //         }
    //         $invoice=Invoice::query()->create($this->input);

    //         if($invoice){
    //             $grn_items=GrnItem::query()->where('grn_id',$request->grn_id)->get();
    //             foreach($grn_items as $items){
    //                 $itemInsert=array(
    //                     'invoice_id'=>$invoice->id,
    //                     'item_qty'=>$items->required_quantity,
    //                     'unit_price'=>$items->unit_price,
    //                     'item_id'=>$items->item_id,
    //                 );
    //                 InvoiceDetail::query()->create($itemInsert);
    //             }

    //         }
    //         DB::commit();

    //         return resp('1', 'Invoice added Successfully!', $invoice->load('invoiceItems.itemDetail','vendorDetail'), Response::HTTP_CREATED);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
    public function store(Request $request)
    {
        $this->authorizeAny([
            'invoice_create',
            'auction_invoices_create',
            'manage_payment_requests',
            'manage_vendor_portal',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Validation
            $request->validate([
                'invoice_date'   => 'required|date',
                'invoice_amount' => 'required|numeric',
                'grn_id'         => 'required|integer',
            ]);

            $grn = GRN::findOrFail($request->grn_id);

            // ✅ Load GRN with relations
            $grnWithRelations = GRN::with([
                'poDetails.rfqDetail.purchase_request.project:id',
                'poDetails.tenderDetails.purchaseRequest.project:id',
            ])->findOrFail($request->grn_id);

            // ✅ Figure out project_id (either rfq path or tender path)
            $projectId = null;

            if ($grnWithRelations->poDetails?->rfqDetail?->purchase_request?->project) {
                $projectId = $grnWithRelations->poDetails->rfqDetail->purchase_request->project->id;
            } elseif ($grnWithRelations->poDetails?->tenderDetails?->purchaseRequest?->project) {
                $projectId = $grnWithRelations->poDetails->tenderDetails->purchaseRequest->project->id;
            }

            if (!$projectId) {
                throw new \Exception('No related project found for this GRN.');
            }

            // ✅ Prepare invoice input
            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO = 'INV/' . sprintf('%04d', $statement[0]->nextID);

            $this->input['project_id']     = $projectId;
            $this->input['invoice_number'] = $inVNO;
            $this->input['grn_id']         = $request->grn_id;
            $this->input['supplier_id']    = $grn->vendor_id;
            $this->input['invoice_status'] = 4;
            $this->input['invoice_date']   = date('Y-m-d H:i:s', strtotime($request->invoice_date));

            // ✅ Handle invoice file
            if ($request->hasFile('invoice_file')) {
                $responce = $this->saveInvoiceFile($request, 'invoice');
                if ($responce) {
                    $this->input['invoice_file'] = $responce;
                }
            } else {
                unset($this->input['invoice_file']);
            }

            // ✅ Create Invoice
            $invoice = Invoice::create($this->input);

            if ($invoice) {
                $grn_items = GrnItem::where('grn_id', $request->grn_id)->get();

                foreach ($grn_items as $items) {
                    $itemInsert = [
                        'invoice_id' => $invoice->id,
                        'item_qty'   => $items->required_quantity,
                        'unit_price' => $items->unit_price,
                        'item_id'    => $items->item_id,
                    ];
                    InvoiceDetail::create($itemInsert);
                }
            }

            DB::commit();

            return resp(
                '1',
                'Invoice added Successfully!',
                $invoice->load('invoiceItems.itemDetail', 'vendorDetail'),
                Response::HTTP_CREATED
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(
                '0',
                'Failed to create record. Error: ' . $e->getMessage(),
                null,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function saveInvoiceFile($request,$folder){

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

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorizeAny([
            'invoice_view',
            'manage_audit_procurement',
            'auction_invoices_view',
            'manage_audit_accounting_bookkeeping',
            'manage_vendor_portal',
        ]);
        $data['invoice']=$invoice->load(
                            [
                                'vr',
                                'invoiceVRDetail',
                                'invoiceFuelRequest',
                                'atr',
                                'invoiceAtrDetail',
                                'vehicleMaintenanceForm',
                                'invoiceVehicleMaintenanceDetail',
                                'invoiceAudit.verifiedBy',
                                'invoiceItems.itemDetail',
                                'grn' => ['grnItem',
                                'poDetails' => ['PoItems','tenderDetails.purchaseRequest', 
                                'rfqDetail' => ['vendor_quotations', 'purchase_request']]] ,
                                'vendorDetail',
                                'consultantContract.CcItems',
                                'workOrder' => ['WoItems', 'tenderDetail.purchaseRequest', 'rfqDetail.purchase_request'],
                                'rfq.disposeRequest','ProjectId',
                                'eventManagement',
                                'invoiceEventManagementDetail',


                            ]);
        $data['las_configuration']=LasConfiguration::query()->with('bankInfo')->get();

        return resp(1,'Successful!', $data,Response::HTTP_OK);    
    }

    public function showInvoice($id)
    {
    $this->authorizeAny([
            'invoice_view',
            'manage_audit_procurement',
            'auction_invoices_view',
            'manage_audit_accounting_bookkeeping',
            'manage_vendor_portal',
        ]);
        $data['invoice'] = Invoice::query()->with(['invoiceItems.itemDetail','vendorDetail'])->findOrFail($id);
        //$data['invoice'] = $invoice->load('invoiceItems.itemDetail','grn.grnItem','vendorDetail','consultantContract.CcItems','workOrder.WoItems','rfq.disposeRequest');
        $data['grnDetail'] = GRN::query()->with('grnItem.itemDetail','vendorDetail','poDetails')->findOrFail($data['invoice']->grn_id);
        $data['grn_approval_request_status']=checkApprovalRequestStatus(36,$data['invoice']->grn_id);
        $data['view_purchase_order'] = PurchaseOrder::query()->with(['PoItems.poItmes'])->findOrFail($data['grnDetail']->po_id);
        $data['rfq_detail'] = PurchaseRequestRfq::query()->findOrFail($data['view_purchase_order']->rfq_id);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeAny([
            'invoice_update'
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorizeAny([
            'invoice_delete'
        ]);

        $invoice->invoiceItems()->delete();
        $invoice->delete();
        return resp(1,'Successful!', [],Response::HTTP_OK);
    }

    public function getApprovedGrnList()
    {
        $this->authorizeAny([
            'manage_vendor_portal',
        ]);
        $data['approved_grn_list']=GRN::query()->with(['grnItem.itemDetail','vendorDetail','poDetails'])

            ->where('approval_status', 1)
            ->where('vendor_id', auth()->user()->vendor_id)
            ->whereDoesntHave('grnInvoices')
            ->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function saveInvoice(Request $request)
    {
        $request->validate([
            'invoice_date' => 'required|date',
            'work_order_id' => 'nullable|integer',
            'invoice_amount' => 'required|numeric',
            'consultant_contract_id' => 'nullable|integer',
            'invoice_file' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $invoiceData = $this->prepareInvoiceData($request);

            if ($request->hasFile('invoice_file')) {
                $invoiceData['invoice_file'] = $this->saveInvoiceFile($request, 'invoice');
            }

            $invoice = Invoice::query()->create($invoiceData);

            if ($invoice) {
                $this->createInvoiceItems($request, $invoice);
            }

            DB::commit();

            return resp('1', 'Invoice added Successfully!', $invoice->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function prepareInvoiceData(Request $request)
    {
        $invoiceData = [
            'invoice_date' => date('Y-m-d', strtotime($request->invoice_date)),
            'invoice_status' => 4,
            'invoice_number' => $this->generateInvoiceNumber()
        ];

        if ($request->work_order_id) {
            $workOrder = WorkOrder::findOrFail($request->work_order_id);
            $invoiceData['work_order_id'] = $request->work_order_id;
            $invoiceData['supplier_id'] = $workOrder->vendor_id;
        } elseif ($request->consultant_contract_id) {
            $consultantContract = ConsultantContract::findOrFail($request->consultant_contract_id);
            $invoiceData['consultant_contract_id'] = $request->consultant_contract_id;
            $invoiceData['supplier_id'] = $consultantContract->vendor_id;
        }

        return $invoiceData;
    }

    private function generateInvoiceNumber()
    {
        $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
        return 'INV/' . sprintf('%04d', $statement[0]->nextID);
    }

    private function createInvoiceItems(Request $request, Invoice $invoice)
    {
        $invoiceItems = collect();

        if ($request->work_order_id) {
            $invoiceItems = WorkOrderDetail::where('work_order_id', $request->work_order_id)->get();
        } elseif ($request->consultant_contract_id) {
            $invoiceItems = ConsultantContractDetail::where('consultant_contract_id', $request->consultant_contract_id)->get();
        }

        foreach ($invoiceItems as $item) {
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'item_qty' => $item->required_quantity,
                'unit_price' => $item->unit_price,
                'item_id' => $item->item_id,
            ]);
        }
    }

    public function getPendingInvoices(Request $request)
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'manage_audit_accounting_bookkeeping',
        ]);

        try {
            $data['invoice_list']=Invoice::query()->with('invoiceItems.itemDetail','grn.grnItem','vendorDetail','consultantContract.CcItems','workOrder.WoItems')->where('is_voucher_posted',0)->get();
            $data['payroll_listing']=EmployeePayrollMaster::query()->with('payrollDetail.allowanceDeduction','createdBy')->where('is_voucher_posted',0)->orderByDesc('id')->get();
            return resp(1,'Successful!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPayrollVoucher(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|integer',
        ]);

        try {
            $payroll_master=EmployeePayrollMaster::query()->selectRaw('*, (TotalNetPay + TotalDeductions) as grand_sum')->where('id',$request->payroll_id)->first();
            $payroll_detail=EmployeePayrollDetail::query()->select('id')->where('PayrollMasterId',$request->payroll_id)->get();
            $payroll_detail = $payroll_detail->pluck('id');
            $allowance_deduction_heads=SalaryAccountHeadSetting::all();
            $account_heads=[];
            if($allowance_deduction_heads ){
                foreach($allowance_deduction_heads as $key => $allowance_deduction){
                    $account_detail=ChartOfAccount::query()->where('id',$allowance_deduction['account_head_id'])->first();
                    if($account_detail){
                        $account_heads[$key]['id']=$account_detail->id  ?? NULL;
                        $account_heads[$key]['head_name']=$account_detail->name ?? NULL;
                        $account_heads[$key]['code']=$account_detail->code  ?? NULL;
                        $account_heads[$key]['amount']=SalaryAllowanceDeduction::query()->whereIn('PayrollDetailId',$payroll_detail)->sum('Value');
                        $account_heads[$key]['type']='Allowance & Deduction';
                    }

                }

            }
            $tax=[];

            $salaryaccount_configuration=SalaryAccountConfiguration::query()->where('chart_of_account_code','')->count();
            if($salaryaccount_configuration){
                return resp('0', 'Salary account configuration is not completed.', [], Response::HTTP_OK);
            }else{
                $account_heads=array();
                $salaryaccount_configuration=SalaryAccountConfiguration::query()->with('ChartOfAccountCode')->get();
                $loan_total=0;
                $tax_total=0;
                $Eobi_total=0;
                foreach($salaryaccount_configuration as $key => $configuration){


                    if($configuration->id == 4){
                        $tax_amount=SalaryAllowanceDeduction::query()->whereIn('PayrollDetailId',$payroll_detail)->where('Description','TAX')->sum('Value');


                        $tax['head_name']=$configuration->ChartOfAccountCode->name  ?? NULL;
                        $tax['code']=$configuration->ChartOfAccountCode->code  ?? NULL;
                        $tax['id']=$configuration->ChartOfAccountCode->id  ?? NULL;
                        $tax['amount']=$tax_amount;
                        $tax['type']='TAX';
                        $account_heads[]=$tax;
                        $tax_total=$tax_amount;
                    } else if($configuration->id == 2){
                        $eobi_amount=SalaryAllowanceDeduction::query()->whereIn('PayrollDetailId',$payroll_detail)->where('Description','Provident Fund')->sum('Value');


                        $eobi['head_name']=$configuration->ChartOfAccountCode->name  ?? NULL;
                        $eobi['code']=$configuration->ChartOfAccountCode->code  ?? NULL;
                        $eobi['id']=$configuration->ChartOfAccountCode->id  ?? NULL;
                        $eobi['amount']=$eobi_amount;
                        $eobi['type']='EOBI';
                        $account_heads[]=$eobi;
                        $Eobi_total=$eobi_amount;
                    }else if($configuration->id == 3){
                        $loan_amount=EmployeePayrollDetail::query()->where('PayrollMasterId',$request->payroll_id)->sum('unpaidInstallment');


                        $loan['head_name']=$configuration->ChartOfAccountCode->name  ?? NULL;
                        $loan['code']=$configuration->ChartOfAccountCode->code  ?? NULL;
                        $loan['id']=$configuration->ChartOfAccountCode->id  ?? NULL;
                        $loan['amount']=$loan_amount;
                        $loan['type']='Loan';
                        $account_heads[]=$loan;
                        $loan_total=$loan_amount;
                    }else if($configuration->id == 1){
                        $salary['head_name']=$configuration->ChartOfAccountCode->name  ?? NULL;
                        $salary['code']=$configuration->ChartOfAccountCode->code  ?? NULL;
                        $salary['id']=$configuration->ChartOfAccountCode->id  ?? NULL;
                        $salary['amount']=0;
                        $salary['type']=$configuration->account_title  ?? NULL;
                        $account_heads[]=$salary;
                    }else{
                        $tax['head_name']=$configuration->ChartOfAccountCode->name  ?? NULL;
                        $tax['code']=$configuration->ChartOfAccountCode->code  ?? NULL;
                        $tax['id']=$configuration->ChartOfAccountCode->id  ?? NULL;
                        $tax['amount']=0;
                        $tax['type']=$configuration->account_title  ?? NULL;
                        $account_heads[]=$tax;
                    }


                }


                $index = array_search('SALARY', array_column($account_heads, 'type'));

                $payroll_master['salary_amount']=$salary_amount=$payroll_master['grand_sum'] - $loan_total - $tax_total - $Eobi_total;
                $account_heads[$index]['amount']=$salary_amount;
                $data['payroll_master']=$payroll_master;
                $data['account_heads']=$account_heads;

                $payrollDetail = DB::select('EXEC PayrollVoucher ?', [$request->payroll_id]);
                $data['payrollDetail']=$payrollDetail;
                return resp('1', 'Invoice added Successfully!', $data, Response::HTTP_OK);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createAuctionInvoice(Request $request)
    {
        $request->validate([
            'invoice_date' => 'required|date',
            'rfq_id' => 'required|integer|exists:purchase_request_rfqs,id',
            'vendor_id' => 'required|integer',
        ]);

        $invoice = Invoice::query()->where('rfq_id', $request->rfq_id)
            ->where('supplier_id', $request->vendor_id)->first();
        if ($invoice){
            return resp('0', 'Invoice already created!', [], Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['invoice_number']=$inVNO;
            $this->input['rfq_id']=$request->rfq_id;
            $this->input['supplier_id']=$request->vendor_id;
            $this->input['invoice_status']=4;
            $this->input['invoice_date']=date('Y-m-d',strtotime($request->invoice_date));
            if($request->hasFile('invoice_file')) {

                $responce = $this->saveInvoiceFile($request, 'invoice');

                if ($responce) {
                    $this->input['invoice_file'] = $responce;
                }
            }else{
                unset($this->input['invoice_file']);
            }
            $invoice=Invoice::query()->create($this->input);

            if ($invoice) {
                $rfq = PurchaseRequestRfq::query()
                    ->with(['vendor_quotations' => function ($query) use ($request) {
                        $query->where('vendor_id', $request->vendor_id)->limit(1);
                    }, 'vendor_quotations.quotationItems'])
                    ->find($request->rfq_id);

                $quotation = $rfq?->vendor_quotations?->first();
                //dd($quotation->toArray());

                if ($quotation) {
                    foreach ($quotation->quotationItems as $item) {
                        $itemInsert = array(
                            'invoice_id' => $invoice->id,
                            'item_qty' => 1,
                            'unit_price' => $item->bid_price,
                            'item_id' => $item->item_id,
                            'item_variant_id' => $item->item_variant_id,
                        );
                        InvoiceDetail::query()->create($itemInsert);
                    }
                }
            }
            DB::commit();

            return resp('1', 'Invoice added Successfully!', $invoice->load('invoiceItems.itemDetail','vendorDetail'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function verifiedInvoice(Request $request,Invoice $invoice)
    {
        $request->validate([
            'is_verified' => 'required|integer'
        ]);
        try {
            DB::beginTransaction();
            $invoice->update([
                'invoice_status'=>$request->is_verified
            ]);
            InvoiceAudit::create([
                'invoice_id'=>$invoice->id,
                'is_verified'=>$request->is_verified,
                'verified_by'=>auth()->user()->id,
                'reason'=>$request->reason
            ]);
            DB::commit();
            return resp(1, 'Successful!', $invoice, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}
