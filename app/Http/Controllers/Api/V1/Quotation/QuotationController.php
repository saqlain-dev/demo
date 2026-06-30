<?php

namespace App\Http\Controllers\Api\V1\Quotation;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Configuration\GeneratedLetter;
use App\Models\Customer;
use App\Models\EmailTemplate;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\Quotation\Quotation;
use App\Models\Quotation\QuotationDetail;
use App\Models\Quotation\QuotationTermCondition;
use App\Models\Supplier\Supplier;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'quotation_view',
        ]);
        $data['quotation_list']=Quotation::query()->with('customer','quotationStatus','opportunity','quotationDetail')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'quotation_create',
        ]);
        $request->validate([
            //'quotation_series' => 'required',
            'quotation_status' => 'required|integer',
            'customer_id' => 'required|integer',
            //'opportunity_id' => 'required|integer',
            //'date' => 'required|date_format:Y-m-d',
            'valid_till_date' => 'required|date_format:Y-m-d',
           // 'items' => 'required|array|min:1', // Ensures at least one item
            'items.*.item_name' => 'required|string',
            'items.*.item_quantity' => 'required|numeric',
            'items.*.uom' => 'required|integer',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $quoItems=$request->items;
            $statement = DB::select("SELECT IDENT_CURRENT('quotations') as nextID");
            $quotation_series_number='QUO-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['quotation_series']=$quotation_series_number;
            $quotation=Quotation::query()->create($this->input);
            if($quotation && $quoItems){
                foreach($quoItems as $key => $items){

                    $quoItem=array(
                        'item_name'=>$items['item_name'],
                        'item_quantity'=>$items['item_quantity'],
                        'uom'=>$items['uom'],
                        'rate'=>$items['rate'],
                        'amount'=>$items['amount'],
                        'margin_rate'=>$items['margin_rate'],
                        'quotation_id'=>$quotation->id,
                        'rfp_item_id'=>$items['rfp_item_id'],
                        'created_by'=>auth()->user()->id
                    );


                    $quoItems=QuotationDetail::query()->insert($quoItem);
                }
            }
            $sum = QuotationDetail::where('quotation_id', $quotation->id)->sum('amount');
            Quotation::query()->where('id',$quotation->id)->update(array('quotation_amount'=>$sum));

            DB::commit();
            return resp(1, 'Successful!', $quotation->load('customer','quotationStatus','opportunity','quotationDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function addTermCondition(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|integer',
            'letter_id' => 'required|integer',
            'term_condition' => 'required',
            'letter_type' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            $quotationtermCondition=QuotationTermCondition::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $quotationtermCondition->load('quotation','generatedLetter'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function updateTermCondition(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|integer',
            'letter_id' => 'required|integer',
            'term_condition' => 'required',
            'letter_type' => 'required|integer',
            'term_condition_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            QuotationTermCondition::query()->find($request->term_condition_id)->update($this->input);
            $quotationtermCondition=QuotationTermCondition::query()->find($request->term_condition_id);
            DB::commit();
            return resp(1, 'Successful!', $quotationtermCondition->load('quotation','generatedLetter'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Quotation $quotation)
    {
        $this->authorizeAny([
            'quotation_view',
        ]);
        $data['quotation']=$quotation->load(['customer','quotationStatus','opportunity','quotationDetail'=>['itemDetail','uom'],'termCondition','proposal','created_by'=>['employeeDetail'=>['department','designation']],'purchaseOrder'=>['purchaseOrderDetail.item','purchaseOrderDetail.uom','salesOrder'=>['salesOrderItems'=>['item','uom'],'orderType','customer']],'comments.createdBy','rfp'=>['rfpDetail.item','rfpDetail.division','rfpDetail.uom','rfpStatus']]);
        $data['approval_request'] = getNextApproval(62, auth()->user()->designation_id, $quotation->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(62, $quotation->id);
        return resp(1, 'Successful!',$data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeAny([
            'quotation_update',
        ]);
        $request->validate([
            //'quotation_series' => 'required',
            'quotation_status' => 'required|integer',
            'customer_id' => 'required|integer',
            //'opportunity_id' => 'required|integer',
            //'date' => 'required|date_format:Y-m-d',
            'valid_till_date' => 'required|date_format:Y-m-d',
            'items.*.item_name' => 'required|string',
            'items.*.item_quantity' => 'required|numeric',
            'items.*.uom' => 'required|integer',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $quoItems=$request->items;
            $quotation->update($this->input);

            if($quoItems){
                foreach($quoItems as $key => $items){

                    $quoItem=array(
                        'item_name'=>$items['item_name'],
                        'item_quantity'=>$items['item_quantity'],
                        'uom'=>$items['uom'],
                        'rate'=>$items['rate'],
                        'amount'=>$items['amount'],
                        'margin_rate'=>$items['margin_rate'],
                        'quotation_id'=>$quotation->id,
                        'created_by'=>auth()->user()->id
                    );

                    if(isset($items['quotation_item_id']) && $items['quotation_item_id'] != ""){
                        $quoItems=QuotationDetail::query()->find($items['quotation_item_id'])->update($quoItem);
                    }else{
                        $quoItems=QuotationDetail::query()->insert($quoItem);
                    }

                }

                $sum = QuotationDetail::where('quotation_id', $quotation->id)->sum('amount');
                Quotation::query()->where('id',$quotation->id)->update(array('quotation_amount'=>$sum));
            }

            DB::commit();
            return resp(1, 'Successful!', $quotation->load('customer','quotationStatus','opportunity','quotationDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quotation $quotation)
    {
        $this->authorizeAny([
            'quotation_delete',
        ]);
        $quotation->quotationDetail()->delete();
        $quotation->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getQuotationDropDown()
    {

        $data['uom']=Type::getTypeValues('uom');
        $data['quotation_status']=Type::getTypeValues('quotation-status');
        $data['supplier_list']=Supplier::query()->with('supplierGroup','supplierType','country')->get();
        $data['customer_list']=Customer::query()->with('customerType')->get();
        $data['lead_list']=Lead::query()->get();
        $data['opportunity_list']=Opportunity::query()->get();
        $data['general_templates']=GeneralTemplates::query()->get();
        $data['item_category_list']=ErpItemCategory::query()->with('itemSubcategory')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendQuotationForApproval(Quotation $item)
    {
        $template=EmailTemplate::query()->where('id',62)->where('template_for',1)->first();
        $approval_process_name=ApprovalProcessName::query()->where('id',62)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',62)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',62)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                if(!empty($template)){
                    $title="New ".$approval_process->approval_process_name." Approval Required";
                    $message="A new ".$approval_process->approval_process_name." is awaiting your approval. Please review and take action.";
                    sendNotification($approval['designation_id'],$title,$message,$template->template_key);
                }


            }
            $update=array('approval_status'=>2);
            Quotation::query()->where('id',$item->id)->update($update);
            return resp(1,'Quotation send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Quotation approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
