<?php

namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;

use App\Http\Controllers\Api\V1\Program\Project\ProjectProfileController;
use App\Http\Controllers\Controller;
use App\Models\Admin\Procurement;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\Admin\TenderDetail;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Models\PurchaseRequestItems;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PurchaseRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'purchase_request_view',
            'claims_purchase_request_view',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $data['purchaseRequest']=$purchaseRequest=PurchaseRequest::query()->with('prItems.items','department','project','createdBy','procurementPlan')->orderByDesc('id')->get();
        $data['draft']=$purchaseRequest->where('pr_approval_status',4)->count();
        $data['pending']=$purchaseRequest->where('pr_approval_status',2)->count();
        $data['approved']=$purchaseRequest->where('pr_approval_status',1)->count();
        $data['reject']=$purchaseRequest->where('pr_approval_status',3)->count();

        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }
    public function purchaseRequestByUser()
    {
        $this->authorizeAny([
            'purchase_request_view',
            'claims_purchase_request_view',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $userid = auth()->user()->id;

        $data['purchaseRequest']=$purchaseRequest=PurchaseRequest::query()->with('prItems.items','department','project','createdBy','procurementPlan')->orderByDesc('id')->where('created_by', $userid)->get();
        $data['draft']=$purchaseRequest->where('pr_approval_status',4)->count();
        $data['pending']=$purchaseRequest->where('pr_approval_status',2)->count();
        $data['approved']=$purchaseRequest->where('pr_approval_status',1)->count();
        $data['reject']=$purchaseRequest->where('pr_approval_status',3)->count();

        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'purchase_request_create',
            'claims_purchase_request_create',
            'manage_employee_portal',
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                //'project_id' => 'required|integer',
                'department_id' => 'required|integer',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('purchase_requests') as nextID");
            $prNO='RF/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['purchase_request_no']=$prNO;
            $this->input['date']=date('Y-m-d',strtotime($request->date));

            $prequest=PurchaseRequest::query()->create($this->input);
            DB::commit();
            return resp('1', 'Purchase request added Successfully!', $prequest, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        return resp(1,'Successful!', $purchaseRequest,Response::HTTP_CREATED);
    }
    public function addPRItems(Request $request)
    {
        $this->authorizeAny([
            'purchase_request_create',
            'claims_purchase_request_create',
            'manage_employee_portal',
        ]);

        $request->validate([
            'purchase_request_id' => 'required',
            'item_id' => 'required|integer', // validation for item_id (nullable and integer)
            'required_quantity' => 'required|integer',
        ]);

        try {

            DB::beginTransaction();

            $prequestitem=PurchaseRequestDetail::query()->create($this->input);
            $amountSum= PurchaseRequestDetail::query()->where('purchase_request_id',$request->purchase_request_id)->selectRaw('SUM(estimated_total_cost) as totalPRAmount')->first();
            PurchaseRequest::query()->where('id',$request->purchase_request_id)->update(array('total_amount'=>$amountSum->totalPRAmount));
            DB::commit();
            $purchaseRequest=PurchaseRequest::query()->with('prItems.items','department','project')->findOrFail($request->purchase_request_id);
            return resp('1', 'Item added Successfully!', $purchaseRequest, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updatePRItem(Request $request, PurchaseRequestDetail $item)
    {
        $this->authorizeAny([
            'purchase_request_update',
            'claims_purchase_request_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'purchase_request_id' => 'required',
            'item_id' => 'required|integer', // validation for item_id (nullable and integer)
            'required_quantity' => 'required|integer',
        ]);

        try {

            DB::beginTransaction();

            $prequestitem=PurchaseRequestDetail::query()->where('id',$item->id)->update($this->input);
            $amountSum= PurchaseRequestDetail::query()->where('purchase_request_id',$request->purchase_request_id)->selectRaw('SUM(estimated_total_cost) as totalPRAmount')->first();
            PurchaseRequest::query()->where('id',$request->purchase_request_id)->update(array('total_amount'=>$amountSum->totalPRAmount));
            DB::commit();
            $purchaseRequest=PurchaseRequest::query()->with('prItems.items','department','project')->findOrFail($request->purchase_request_id);
            return resp('1', 'Item updated Successfully!', $purchaseRequest, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteItem(PurchaseRequestDetail $item)
    {
        $this->authorizeAny([
            'purchase_request_delete',
            'claims_purchase_request_delete',
            'manage_employee_portal',
        ]);

        $item->delete();
        return resp(1,'Purchase request item deleted successfully.', [],Response::HTTP_OK);
    }
    /**
     * Display the specified resource.
     */
    public function show(PurchaseRequest $purchaseRequest)
    {
        $this->authorizeAny([
            'purchase_request_view',
            'claims_purchase_request_view',
            'manage_audit_procurement',
            'manage_employee_portal',
        ]);

        $data['purchaseRequest']=PurchaseRequest::query()->with(['prItems.items','department','project','createdBy','procurementPlan'])->findOrFail($purchaseRequest->id);
        $data['approval_request']=getNextApproval(8,auth()->user()->designation_id,$purchaseRequest->id);
        $data['approval_request_status']=checkApprovalRequestStatus(8,$purchaseRequest->id);
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $this->authorizeAny([
            'purchase_request_update',
            'claims_purchase_request_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            //'project_id' => 'required|integer',
            'department_id' => 'required|integer',
        ]);

        $this->input['date']=date('Y-m-d',strtotime($request->date));
        PurchaseRequest::query()->where('id',$purchaseRequest->id)->update($this->input);

        $amountSum= PurchaseRequestDetail::query()->where('purchase_request_id',$purchaseRequest->id)->selectRaw('SUM(estimated_total_cost) as totalPRAmount')->first();
        PurchaseRequest::query()->where('id',$purchaseRequest->id)->update(array('total_amount'=>$amountSum->totalPRAmount));


        $purchaseRequest=PurchaseRequest::query()->with(['prItems.items'])->findOrFail($purchaseRequest->id);
        return resp(1,'Successful!', $purchaseRequest,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $this->authorizeAny([
            'purchase_request_delete',
            'claims_purchase_request_delete',
            'manage_employee_portal',
        ]);

        $purchaseRequest->prItems()->delete();
        $purchaseRequest->delete();
        return resp(1,'Purchase request deleted successfully.', [],Response::HTTP_OK);
    }
    public function addPurchaseRequest(){
        $data['categories']=ItemCategory::with('itemSubcategory')->get();
       // $projects=ProjectProfile::approvedProjects();
        $data['departments']= Type::getTypeValues('department-names');
        $data['procurement_plans'] =$Procurement=Procurement::query()->with('items.item','budget.BudgetDetail')->where('approval_status',1)->get();

        $data['projects']=$this->getProjectActivities();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    public function getProcurementPlanItems(Request $request)
    {
        $data['procurement_plans'] =$Procurement=Procurement::query()->where('id',$request->procurement_plan_id)->with('items.item')->get();
        foreach($Procurement as $key => $proc){

            foreach($proc['items'] as  $j => $item){

                $quantity=$this->getPlanRemainingItems($item['procurement_id'],$item['item_id']);
                $proc['items'][$j]['remaining_quantity']=$item['number_of_units'] - $quantity;

            }


        }
        $data['procurement_plans']=$Procurement;
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function getPlanRemainingItems($procurement_id,$item_id)
    {
        return PurchaseRequestDetail::query()->where('procurement_id',$procurement_id)->where('item_id',$item_id)->sum('required_quantity');
    }
    public function getProjectActivities()
    {
        $projects = ProjectProfile::with([
            'progressWorkplans.workPlanGoals.activities',
            'progressWorkplans.workPlanOutcome.activities',
            'progressWorkplans.workPlanOutput.activities'
        ])->get();

        $projects->each(function ($project) {

            $project->activities = collect();

            foreach ($project->progressWorkplans as $workplan) {

                $project->activities = $project->activities->merge($workplan->workPlanGoals->flatMap(function ($goal) {
                    return $goal->activities;
                }));

                $project->activities = $project->activities->merge($workplan->workPlanOutcome->flatMap(function ($outcome) {
                    return $outcome->activities;
                }));

                $project->activities = $project->activities->merge($workplan->workPlanOutput->flatMap(function ($output) {
                    return $output->activities;
                }));
            }
        });

        return $projects;


    }
    public function getItems(Request $request){


        $query=Item::query();
        if($this->input['category_id']){
            $query->where('category_id',$this->input['category_id']);
        }
        if($this->input['sub_category_id']){
            $query->where('sub_category_id',$this->input['sub_category_id']);
        }
        $items=$query->get();
        return resp(1,'Successful!', $items,Response::HTTP_CREATED);
    }

    public function getRemainingItems($id)
    {
        $purchase_request_details = PurchaseRequestDetail::query()->with('items.itemUnit','purchase_request')->where('purchase_request_id', $id)->get();
        $purchase_request_details = $purchase_request_details->filter(function ($item) {
            $rfq_quantity_used = PurchaseRequestRfqDetail::where('purchase_request_detail_id', $item->id)->sum('required_quantity');
            $tender_quantity_used = TenderDetail::where('purchase_request_detail_id', $item->id)->sum('required_quantity');
            $remaining_quantity = $item->required_quantity - $rfq_quantity_used - $tender_quantity_used;

            $item->required_quantity = $remaining_quantity;

            // Keep items with remaining_quantity greater than 0
            return $remaining_quantity > 0;
        });
        $purchase_request_details = $purchase_request_details->values();

        return resp(1, 'Successful!', $purchase_request_details, Response::HTTP_CREATED);
    }

    public function sendPurchaseRequestForApproval(PurchaseRequest $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',8)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',8)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            $update=array('pr_approval_status'=>2);
            PurchaseRequest::query()->where('id',$item->id)->update($update);
            return resp(1,'Purchase request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
