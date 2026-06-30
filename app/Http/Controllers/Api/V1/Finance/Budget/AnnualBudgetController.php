<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\District;
use App\Models\Employee;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AnnualBudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'annual_budget_view',
            'manage_audit_budgeting',
            'budget_view'
        ]);

        $data['list'] = AnnualBudget::with(['BudgetType','created_by', 'updated_by','BudgetDetail' => ['ProjectId','HeadId.AccountTypeId']])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'annual_budget_create',
        ]);

        $request->validate([
            'budget_type' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'project_budgets' => 'array',
            'project_budgets.*' => 'integer|exists:project_budgets,id',
        ]);
        try {
            DB::beginTransaction();
            $item = AnnualBudget::query()->create($this->input);
            $item->projectBudgets()->sync($request->project_budgets);
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
    public function show(AnnualBudget $annualBudget): JsonResponse
    {
        $this->authorizeAny([
            'annual_budget_view',
            'manage_audit_budgeting',
            'budget_view',
        ]);

        $data['annualBudget'] = $annualBudget->load(['BudgetType','created_by', 'updated_by','BudgetDetail' => ['ProjectId','HeadId.AccountTypeId'],'projectBudgets.BudgetDetail.budgetCategory' ,'projectBudgets.BudgetDetail.Head' => ['AccountTypeId', 'parent']]);
        $data['approval_request']=getNextApproval(39,auth()->user()->designation_id,$annualBudget->id);
        $data['approval_request_status']=checkApprovalRequestStatus(39,$annualBudget->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AnnualBudget $annualBudget)
    {
        $this->authorizeAny([
            'annual_budget_update',
        ]);

        $request->validate([
            'budget_type' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'project_budgets' => 'array',
            'project_budgets.*' => 'integer|exists:project_budgets,id',
        ]);
        try {
            DB::beginTransaction();

            $annualBudget->projectBudgets()->sync($request->project_budgets);
            unset($this->input['project_budgets']);

            $item = $annualBudget->update($this->input);
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
    public function destroy(AnnualBudget $annualBudget): JsonResponse
    {
        $this->authorizeAny([
            'annual_budget_delete',
        ]);

        $annualBudget->BudgetDetail()->delete();
        $item = $annualBudget->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function sendAnnualBudgetForApproval(AnnualBudget $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',39)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',39)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',39)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            AnnualBudget::query()->where('id',$item->id)->update($update);
            return resp(1,'Annual Budget request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Annual Budget approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
