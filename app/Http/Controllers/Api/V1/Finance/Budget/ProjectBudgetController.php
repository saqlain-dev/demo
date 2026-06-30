<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Finance\Budget\ProjectBudgetApprovalLog;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\Finance\Budget\ProjectBudgetDetailLog;
use App\Models\Finance\Budget\ProjectBudgetLog;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProjectBudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'program_budget_view',
            'budget_view',
            'manage_audit_budgeting',
            'manage_employee_portal',
        ]);

        $data['list'] = ProjectBudget::with(['ProjectId','created_by', 'updated_by','BudgetDetail.Head' => ['parent','AccountTypeId']])->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'program_budget_create',
            'budget_create',
            'manage_employee_portal',
        ]);


        $request->validate([
            'project_id' => [
                'required',
                Rule::unique('project_budgets', 'project_id')
                    ->whereNull('deleted_at'),
            ],
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'detail' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ProjectBudget::query()->create($this->input);
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
    public function show(ProjectBudget $projectBudget): JsonResponse
    {
        $this->authorizeAny([
            'program_budget_view',
            'budget_view',
            'manage_audit_budgeting',
            'manage_employee_portal',
        ]);

        /*$data['projectBudget'] = $projectBudget->load(['ProjectId','created_by', 'updated_by','BudgetDetail'=>['UnitType','Head' => ['parent','AccountTypeId']], 'BudgetDetail.activity','BudgetDetail.budgetCategory', 'projectBudgetLogs.BudgetDetail.Head' => ['parent','AccountTypeId'], 'projectBudgetLogs.approvalProcessLogs' => ['created_by', 'updated_by', 'designation']]);*/
        $data['projectBudget'] = $projectBudget->load([
            'ProjectId',
            'created_by',
            'updated_by',
            'BudgetDetail' => [
                'UnitType',
                'Head' => function ($query) {
                    $query->with('parent', 'AccountTypeId');
                }
            ],
            'BudgetDetail.activity',
            'BudgetDetail.budgetCategory',
            'projectBudgetLogs.BudgetDetail.Head' => function ($query) {
                $query->with('parent', 'AccountTypeId');
            },
            'projectBudgetLogs.approvalProcessLogs' => [
                'created_by',
                'updated_by',
                'designation'
            ],
        ]);

        $data['approval_request']=getNextApproval(38,auth()->user()->designation_id,$projectBudget->id);
       $data['approval_request_status']=checkApprovalRequestStatus(38,$projectBudget->id);

        // Convert to array format
        $budgetDetails = $projectBudget->BudgetDetail->toArray();
        $projectBudget->BudgetDetail->each(function ($budgetDetail) {

            if ($budgetDetail->Head) {
                $budgetDetail->Head->loadAllParents();
            }
        });
       // dd($budgetDetails);

        // Create a flat list of heads
        $headList = [];
        foreach ($budgetDetails as &$detail) {
            if (isset($detail['head'])) {
                $detail['head']['budget_category']=$detail['budget_category'];
                $headList[$detail['head']['id']] = &$detail['head'];
               // $headList[$detail['head']['id']['category']] = &$detail['budget_category'];
            }
        }




        // Build the tree from the flat list
        $tree = $this->buildTree($headList);

        // Include the tree in the response
        $data['projectBudget'] = $projectBudget;
        $data['budgetDetailsTree'] = $tree;


        $projectBudget->projectBudgetLogs->each(function ($log) {
            $log->BudgetDetail->each(function ($budgetDetail) {
                $budgetDetail->Head->loadAllParents();
            });
        });
        $budgetDetailLogs = $projectBudget->projectBudgetLogs->pluck('BudgetDetail')->flatten()->toArray();

        // Create a flat list of heads for budget detail logs
        $headListLogs = [];
        foreach ($budgetDetailLogs as &$logDetail) {
            if (isset($logDetail['head'])) {
                $headListLogs[$logDetail['head']['id']] = &$logDetail['head'];
            }
        }

        // Build tree for budgetDetailsLogsTree and include in response data
        $data['budgetDetailsLogsTree'] = $this->buildTree($headListLogs);


        return resp('1', 'Successful!', $data, Response::HTTP_OK);
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
    public function update(Request $request, ProjectBudget $projectBudget)
    {
        $this->authorizeAny([
            'program_budget_update',
            'budget_update',
            'manage_employee_portal',
        ]);

        $request->validate([
            'project_id' => [
                'required',
                Rule::unique('project_budgets', 'project_id')
                    ->whereNull('deleted_at')
                    ->ignore($projectBudget->id),
            ],
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'detail' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $projectBudget->update($this->input);
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
    public function destroy(ProjectBudget $projectBudget): JsonResponse
    {
        $this->authorizeAny([
            'program_budget_delete',
            'budget_delete',
        ]);

        $projectBudget->BudgetDetail()->delete();
        $item = $projectBudget->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function sendProjectBudgetForApproval(ProjectBudget $item)
    {


        $item = ProjectBudget::withSum('BudgetDetail', 'program_total')->find($item->id);


        $claimAmount = $item->budget_detail_sum_program_total ?? 0;


        $approval_process_name=ApprovalProcessName::query()->where('id',38)->first();

        $approval_process=ApprovalProcess::query()->where('approval_process_id',38)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',38)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            ProjectBudget::query()->where('id',$item->id)->update($update);
            return resp(1,'Project Budget request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Project Budget approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function realignProjectBudget(Request $request)
    {
        $this->authorizeAny([
            'program_budget_update',
            'budget_update',
        ]);

        $request->validate(['project_budget_id' => 'required']);

        try {
            DB::beginTransaction();

            $projectBudget = ProjectBudget::query()->with('BudgetDetail')->findOrFail($request->project_budget_id);

            $projectBudgetLog = ProjectBudgetLog::query()->create([
                'project_budget_id' => $projectBudget->id,
                'project_id' => $projectBudget->project_id,
                'from_date' => $projectBudget->from_date,
                'to_date' => $projectBudget->to_date,
                'detail' => $projectBudget->detail,
                'total' => $projectBudget->total,
                'status' => $projectBudget->status,
                'approval_status' => $projectBudget->approval_status,
                'log_created_by' => $projectBudget->created_by,
                'log_updated_by' => $projectBudget->updated_by,
                'log_created_at' => $projectBudget->created_at,
                'log_updated_at' => $projectBudget->updated_at,
            ]);

            $projectBudget->BudgetDetail->each(function ($budgetDetail) use ($projectBudgetLog) {
                ProjectBudgetDetailLog::query()->create([
                    'project_budget_log_id' => $projectBudgetLog->id,
                    'category_id' => $budgetDetail->category_id,
                    'sub_category_id' => $budgetDetail->sub_category_id,
                    'unit' => $budgetDetail->unit,
                    'number' => $budgetDetail->number,
                    'amount' => $budgetDetail->amount,
                    'rate' => $budgetDetail->rate,
                    'requested_funds' => $budgetDetail->requested_funds,
                    'cost_shared_applicants' => $budgetDetail->cost_shared_applicants,
                    'program_total' => $budgetDetail->program_total,
                    'sub_total' => $budgetDetail->sub_total,
                    'grand_total' => $budgetDetail->grand_total,
                    'status' => $budgetDetail->status,
                    'budget_for' => $budgetDetail->budget_for,
                    'head_id' => $budgetDetail->head_id,
                    'activity_id' => $budgetDetail->activity_id,
                    'log_created_by' => $budgetDetail->created_by,
                    'log_updated_by' => $budgetDetail->updated_by,
                    'log_created_at' => $budgetDetail->created_at,
                    'log_updated_at' => $budgetDetail->updated_at,
                ]);
            });

            $projectBudgetApprovalLogs = ApprovalProcessList::query()->where('approval_process_id',38)->where('request_module_id', $projectBudget->id)->get();
            $projectBudgetApprovalLogs->each(function ($approvalLog) use ($projectBudgetLog) {
                ProjectBudgetApprovalLog::query()->create([
                    'project_budget_log_id' => $projectBudgetLog->id,
                    'approval_process_id' => $approvalLog->approval_process_id,
                    'designation_id' => $approvalLog->designation_id,
                    'approval_status' => $approvalLog->approval_status,
                    'process_order' => $approvalLog->process_order,
                    'comments' => $approvalLog->comments,
                    'approval_request_status' => $approvalLog->approval_request_status,

                    'log_created_by' => $approvalLog->created_by,
                    'log_updated_by' => $approvalLog->updated_by,
                    'log_created_at' => $approvalLog->created_at,
                    'log_updated_at' => $approvalLog->updated_at,
                ]);
            });

            ApprovalProcessList::query()->where('approval_process_id',38)->where('request_module_id', $projectBudget->id)->delete();
            // $projectBudget->BudgetDetail()->delete();
            // $projectBudget->delete();
            $projectBudget->is_realign = true;
            $projectBudget->approval_status = 4;
            $projectBudget->save();

            DB::commit();
            return resp('1', 'Project Budget realign Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to realign Project Budget!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
    public function getProjectID($project_budget_detId)
    {
        $projectBudget = ProjectBudget::query()->find($project_budget_detId);

        if($projectBudget){
            $NominalClass=HeadClass::query()->where('project_id',$projectBudget->project_id)->first();

            return $NominalClass->id ?? NULL;
        }
        return null;
    }

    public function getProjectBudgetBalance(Request $request)
    {

        $request->validate([
            'project_budget_detail_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $BudgetDetail = ProjectBudgetDetail::query()->where('id',$request->project_budget_detail_id)->first();

            $headId = $BudgetDetail->head_id;
            $ClassId=$this->getProjectID($BudgetDetail->project_budget_id);

            $BudgetDetail->balance = $this->getHeadBalance($headId, $ClassId);

            DB::commit();
            return resp('1', ' Successfully!', $BudgetDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
    function getHeadBalance($headId, $nominalClassId = null)
    {
        // Get the Code of Head ID
        $headCode = ChartOfAccount::where('id', $headId)->value('code');

        if (!$headCode) {
            return 0; // If head not found, return 0
        }

        // Now calculate Debit - Credit for this code
        $query = DB::table('tbl_general_ledger_details as d')
            ->join('tbl_general_ledgers as g', 'g.id', '=', 'd.Gl_Id')
            ->where('d.NominalID', $headCode)
            ->where('g.IsPosted', 1);

        /*if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('g.Date', [$startDate, $endDate]);
        }*/

        if (!empty($nominalClassId)) {
            $query->where('d.NominalClassID', $nominalClassId);
        }

        $result = $query->selectRaw('
            SUM(ISNULL(d.Debit, 0)) as total_debit,
            SUM(ISNULL(d.Credit, 0)) as total_credit
        ')
            ->first();

        $balance = ($result->total_debit ?? 0) - ($result->total_credit ?? 0);

        return $balance;
    }

    public function updateProjectBudgetItem(Request $request)
    {
        $request->validate([
            'audit_status' => 'required|integer',
            'budget_item_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $update=array(
                'audit_status'=>$request->audit_status,
                'audit_remarks'=>$request->audit_remarks,
                'audit_updated_by'=>auth()->user()->employee_id,
                'audit_updated_at'=>date('Y-m-d H:i:s'),
            );
            ProjectBudgetDetail::query()->where('id', $request->budget_item_id)->update($update);
            $item=ProjectBudgetDetail::query()->where('id', $request->budget_item_id)->first();
            DB::commit();
            return resp(1, 'Successful!', $item->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
