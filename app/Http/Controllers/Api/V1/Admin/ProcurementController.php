<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Item;
use App\Models\Type;
use App\Models\User;
use App\Models\Activity;
use App\Models\District;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use App\Models\Admin\RfqType;
use Illuminate\Http\Response;
use App\Models\ApprovalProcess;
use Illuminate\Validation\Rule;
use App\Models\Admin\Procurement;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\FinancialYear;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Http\Controllers\Controller;
use App\Models\Admin\ProcurementDetail;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Program\Project\ProjectProfile;


class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeAny([
            'procurement_plan_view',
            'manage_audit_procurement',
            'manage_employee_portal'
        ]);
        $request->validate([
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
        ]);

        $procurement_plan = Procurement::with(['items' => ['project', 'item','selectionMethod','amountType','procurementMethod','qualificationType'],'budget.BudgetDetail'])
        ->when($request->filled('contract_start_date'), function ($query) use ($request) {
            $query->where('contract_start_date', '>=', $request->contract_start_date);
        })
        ->when($request->filled('contract_end_date'), function ($query) use ($request) {
            $query->where('contract_end_date', '<=', $request->contract_end_date);
        })
        ->orderByDesc('id')->get();
        $procurement_plan->each(function ($record) {
            $record->district_details = District::query()->whereIn('id', $record->districts)->get();
        });
        $data['procurement_plan']=$procurement_plan;
        $data['financial_year']= FinancialYear::with('financialYear')->get();
        $data['draft']=$procurement_plan->where('approval_status',4)->count();
        $data['pending']=$procurement_plan->where('approval_status',2)->count();
        $data['approved']=$procurement_plan->where('approval_status',1)->count();
        $data['reject']=$procurement_plan->where('approval_status',3)->count();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getAllProcurements(Request $request){
        $this->authorizeAny([
            'procurement_plan_view',
            'manage_audit_procurement',
        ]);
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 600);

        $request->validate([
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date',
        ]);

       $procurement_plan = Procurement::with([
            'items' => function ($q) {
                $q->with([
                    'project',
                    'item.itemCategory',
                    'item.subCategory',
                    'item.itemUnit',
                    'item.itemType',
                    'selectionMethod',
                    'amountType',
                    'procurementMethod',
                    'qualificationType',
                    'budgetDetail'
                ]);
            },
            'budget.BudgetDetail',
            'purchaseRequests',
            'purchaseRequests.prItems',
            'purchaseRequests.rfqs' => function ($q) {
                $q->with([
                    'items',
                    'rfType',
                    'vendor_quotations' => function($q){
                        $q->where('apply_status ', 1);
                        $q->where('is_qualified ', 1);
                        $q->with([
                            'vendor', 
                            'quotationItems.item',
                            'awardQuotation'
                        ]);
                    },
                    'awardProject',
                    'quotationType',
                    'workOrder',
                    'purchaseOrder' => function($q){
                        $q->with([
                            'PoItems.poItmes',
                            'grns',
                            'grns.grnItem',
                            'grns.vendorDetail',
                            'grns.grnInvoices.invoiceItems.itemDetail',
                        ]);
                    },
                    'invoices',
                    'invoices.invoiceItems',
                    'invoices.workCompletion',
                    'invoices.workCompletion.vendor',
                    'consultantOrder'
                ]);
            }
        ])
        ->when($request->filled('contract_start_date'), function ($query) use ($request) {
            $query->where('contract_start_date', '>=', $request->contract_start_date);
        })
        ->when($request->filled('contract_end_date'), function ($query) use ($request) {
            $query->where('contract_end_date', '<=', $request->contract_end_date);
        })
        ->orderByDesc('id')
        ->get();

        $procurement_plan->each(function ($record) {
            $record->district_details = District::query()->whereIn('id', $record->districts)->get();
        });
        $data['procurement_plan']=$procurement_plan;
        $data['financial_year']= FinancialYear::with('financialYear')->get();
        $data['draft']=$procurement_plan->where('approval_status',4)->count();
        $data['pending']=$procurement_plan->where('approval_status',2)->count();
        $data['approved']=$procurement_plan->where('approval_status',1)->count();
        $data['reject']=$procurement_plan->where('approval_status',3)->count();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'procurement_plan_create'
        ]);

        $request->validate([
            'description' => 'nullable|string',
            //'qualification_type_id' => 'required|integer|exists:type_values,id',
            //procurement_method' => 'required|integer|exists:rfq_types,id',
            'districts' => 'required|array',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'required|date|after_or_equal:contract_start_date',
            'comments' => 'required|string',
            'program_budget_id' => [
                'required',
                'integer',
                Rule::unique('procurements', 'program_budget_id')->whereNull('deleted_at')
            ],
            'sub_total' => 'nullable|numeric', // Adjust precision as needed
        ]);
        try {
            DB::beginTransaction();
            // Calculate subtotal
            $subTotal = collect($request->items)->sum('estimated_amount');
            $this->input['sub_total'] = $subTotal;
            // Create Procurement instance
            $procurement = Procurement::query()->create($this->input);
            DB::commit();
            return resp(1, 'Successful!', $procurement->load('items'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // $this->authorizeAny([
        //     'procurement_plan_view',
        //     'manage_audit_procurement',
        // ]);
        $procurement = Procurement::with(['items' => ['project', 'item.itemCategory','item.subCategory','item.itemUnit','item.itemType','selectionMethod','amountType','procurementMethod','qualificationType'],'items.HeadId','budget.BudgetDetail.activity' ,'budget.BudgetDetail.Head' => ['parent','AccountTypeId'],'budget.ProjectId'])->findOrFail($id);
        
        // Convert to array format
        $budgetDetails = $procurement->budget->BudgetDetail->toArray();
        //dd($procurement->Budget->toArray());
        $procurement->budget->BudgetDetail->each(function ($budgetDetail) {

            if ($budgetDetail->Head) { // Check if Head exists
                $budgetDetail->Head->loadAllParents();
            }
        });
        // Create a flat list of heads
        $headList = [];
        foreach ($budgetDetails as &$detail) {
            if (isset($detail['head'])) {
                $headList[$detail['head']['id']] = &$detail['head'];
            }

        }
        // Build the tree from the flat list
        $tree = $this->buildTree($headList);
        // Include the tree in the response
        $data['budgetDetailsTree'] = $tree;
        //dd($data['budgetDetailsTree']);
        $procurement->district_details = District::query()->whereIn('id', $procurement->districts)->get();
        $data['approval_request']=getNextApproval(13,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(13,$id);
        $data['procurement']=$procurement;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function buildTree(&$headList)
    {
        $tree = [];
        $references = [];

        // Create references for each node
        foreach ($headList as &$head) {
            $references[$head['id']] = &$head;
            $head['children'] = [];
        }

        // Build the tree by linking children to their parents
        foreach ($headList as &$head) {
            if ($head['parent_id'] == 0 || $head['parent_id'] === null) {
                // Root node
                $tree[] = &$head;
            } else {
                // Ensure parent exists in references before linking
                if (isset($references[$head['parent_id']])) {
                    $references[$head['parent_id']]['children'][] = &$head;
                } else {
                    // If parent doesn't exist, consider as root node
                    $tree[] = &$head;
                }
            }
        }

        return $tree;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Procurement $procurement)
    {
        $this->authorizeAny([
            'procurement_plan_update'
        ]);

        $request->validate([
            'description' => 'nullable|string',
            //'qualification_type_id' => 'required|integer|exists:type_values,id',
            //'procurement_method' => 'required|integer|exists:rfq_types,id',
            'districts' => 'required|array',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'required|date|after_or_equal:contract_start_date',
            'comments' => 'required|string',
            'program_budget_id' => [
                'required',
                'integer',
                Rule::unique('procurements', 'program_budget_id')
                    ->ignore($procurement->id) // Ignore the current record
                    ->whereNull('deleted_at') // Ignore soft-deleted records
            ],
        ]);
        try {

            DB::beginTransaction();

            Procurement::query()->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $procurement->load(['items' => ['project', 'item'], 'procurementMethod', 'qualificationType']), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Procurement $procurement)
    {
        $this->authorizeAny([
            'procurement_plan_delete'
        ]);

        $procurement->items()->delete();
        $procurement->delete();
        $data['message'] = "Procurement Deleted Successfully";
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function addItem(Request $request)
    {
        $this->authorizeAny([
            'procurement_plan_create'
        ]);    
        $request->validate([
            //'item_id' => 'required|integer|exists:items,id',
            'procurement_id' => 'required|integer|exists:procurements,id',
            'budget_details_id' => 'nullable|integer',
            'project_id' => 'required|integer|exists:project_profiles,id',
            //'budget_number' => 'required|string',
            'account_id' => 'required',
            'number_of_trainings' => 'nullable|integer',
            'number_of_days' => 'nullable|integer',
            'estimated_amount' => 'required|numeric', // Ensure estimated_amount is numeric
            'comments' => 'required|string',
        ]);
        
        try { 
            DB::beginTransaction(); 
            $item = ProcurementDetail::query()->create($request->all());
            $totalAmount = ProcurementDetail::query()->where('procurement_id', $request->procurement_id)->sum('estimated_amount');
            Procurement::query()->find($request->procurement_id)->update(['sub_total' => $totalAmount]);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
    public function addItems(Request $request)
    {
        $this->authorizeAny(['procurement_plan_create']);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.procurement_id' => 'required|integer|exists:procurements,id',
            'items.*.budget_details_id' => 'nullable|integer',
            'items.*.project_id' => 'required|integer|exists:project_profiles,id',
            'items.*.account_id' => 'required',
            'items.*.number_of_trainings' => 'nullable|integer',
            'items.*.number_of_days' => 'nullable|integer',
            'items.*.estimated_amount' => 'required|numeric',
            'items.*.comments' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $createdItems = [];

            // Keep track of procurement IDs to recalculate sub_totals later
            $procurementIds = [];

            foreach ($request->items as $itemData) {
                $item = ProcurementDetail::create($itemData);
                $createdItems[] = $item;

                $procurementIds[] = $itemData['procurement_id'];
            } 
            foreach (array_unique($procurementIds) as $procurementId) {
                $totalAmount = ProcurementDetail::where('procurement_id', $procurementId)->sum('estimated_amount');
                Procurement::where('id', $procurementId)->update(['sub_total' => $totalAmount]);
            }

            DB::commit();

            return resp(1, 'All items added successfully!', $createdItems, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to add items!', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function updateItem(Request $request, ProcurementDetail $item)
    {
        $this->authorizeAny([
            'procurement_plan_update'
        ]);
        $request->validate([
            //'item_id' => 'required|integer|exists:items,id',
            'procurement_id' => 'required|integer|exists:procurements,id',
            'project_id' => 'required|integer|exists:project_profiles,id', 
            'budget_details_id' => 'nullable|integer',
            //'budget_number' => 'required|string',
            'account_id' => 'required',
            'number_of_trainings' => 'nullable|integer',
            'number_of_days' => 'nullable|integer',
            'estimated_amount' => 'required|numeric', // Ensure estimated_amount is numeric
            'comments' => 'required|string',
        ]);
        try {
            DB::beginTransaction();
            $item->update($request->all());
            $totalAmount = ProcurementDetail::query()->where('procurement_id', $request->procurement_id)->sum('estimated_amount');
            Procurement::query()->find($request->procurement_id)->update(['sub_total' => $totalAmount]);
            DB::commit();
            return resp(1, 'Successful!', $item->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getItem($item)
    {
        $this->authorizeAny([
            'procurement_plan_view'
        ]);

        $data = ProcurementDetail::with('item','selectionMethod','amountType','procurementMethod')->findOrFail($item);
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    public function deleteItem(ProcurementDetail $item)
    {
        $this->authorizeAny([
            'procurement_plan_delete'
        ]);

        $item->delete();
        $data['message'] = "Item Deleted Successfully";
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getDropDowns()
    {
        $data['districts'] = District::all();
        $data['project_budget_unit_type'] = Type::getTypeValues('project-budget-unit-type');
        $data['qualification_types'] = Type::getTypeValues('qualification-types');
        $data['selection_methods'] = Type::getTypeValues('selection-methods');
        $data['amount_types'] = Type::getTypeValues('amount-types');
        $data['procurement_methods'] = RfqType::all();  

        $data['projects'] = ProjectProfile::all();
        $data['items'] = Item::all();
        $data['item_category'] = ItemCategory::all();
        $data['program_budget'] = ProjectBudget::query()->with(['ProjectId','BudgetDetail.head_id','BudgetDetail.UnitType','BudgetDetail.activity'])
                                    ->get(); 
    foreach ($data['program_budget'] as $budget) {
        foreach ($budget->BudgetDetail as $detail) {
            $detail->estimated_amount_sum = $detail->procurementDetail->sum('estimated_amount');
        }
    }

        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    public function getHeadByProgramBudget(Request $request)
    {
        $data['program_budget'] = ProjectBudget::query()->where('id',$request->program_budget_id)->with('BudgetDetail.HeadId')->get();

        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    public function sendProcurementPlanForApproval(Procurement $procurement)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',13)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',13)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',13)->where('approval_request_status',1)->where('request_module_id',$procurement->id)->count();
        if($approval_process->count() > 0  && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$procurement->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $procurement->sub_total  ){
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
            Procurement::query()->where('id',$procurement->id)->update($update);
            return resp(1,'Procurement plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Procurement plan approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function updateProcurementItem(Request $request)
    {
        $request->validate([
            'audit_status' => 'required|integer',
            'procurement_item_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $update=array(
                'audit_status'=>$request->audit_status,
                'audit_remarks'=>$request->audit_remarks,
                'audit_updated_by'=>auth()->user()->employee_id,
                'audit_updated_at'=>date('Y-m-d H:i:s'),
            );
            ProcurementDetail::query()->where('id', $request->procurement_item_id)->update($update);
            $item=ProcurementDetail::query()->where('id', $request->procurement_item_id)->first();
            DB::commit();
            return resp(1, 'Successful!', $item->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


}
