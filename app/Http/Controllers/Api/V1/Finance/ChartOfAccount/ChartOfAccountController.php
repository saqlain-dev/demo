<?php

namespace App\Http\Controllers\Api\V1\Finance\ChartOfAccount;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Country;
use App\Models\Donar\DonarProfile;
use App\Models\Employee;
use App\Models\Province;
use App\Models\Finance\BankInfo;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Finance\Budget\BudgetCategory;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\ChartOfAccountClass;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\Grants\Nofo;
use App\Models\Finance\Voucher\Voucher;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'chart_accounts_view',
        ]);

        $data['list'] = ChartOfAccount::with(['AccountTypeId','parent','ClassId.HeadClassId','created_by', 'updated_by'])->where('approval_status',1)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function pendingChartOfAccounts()
    {
        $this->authorizeAny([
            'manage_finance_configuration',
        ]);

        $data['list'] = ChartOfAccount::with(['AccountTypeId','parent','ClassId.HeadClassId','created_by', 'updated_by'])->get();
        $data['list']->each( function ($record){
            $record->approval_request = getNextApproval(43,auth()->user()->designation_id,$record->id);
            $record->approval_request_status=checkApprovalRequestStatus(43,$record->id);
        });
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'chart_accounts_create',
        ]);

        $request->validate([
            'name' => 'required',
            'code' => 'required|string|digits_between:1,255|unique:chart_of_accounts,code',
            'account_type_id' => 'required',
            'parent_id' => 'required',
            //'class_id' => 'required',
            //'description' => 'required',
        ]);
        $this->input['approval_status'] = 4;
        if (isset($this->input['class_id'])) {
            $classes = $this->input['class_id'];
            unset($this->input['class_id']);
        }else{
            $classes=[];
        }
        try {
            DB::beginTransaction();
            $item = ChartOfAccount::query()->create($this->input);
            if ($item){
                if ($classes){
                    $item->ClassId()->createMany($classes);
                }
            }
            $item = $item->load('ClassId');
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount): JsonResponse
    {
        $this->authorizeAny([
            'chart_accounts_view',
            'manage_finance_configuration',
        ]);

        $data['chartOfAccount'] = $chartOfAccount->load(['AccountTypeId','parent','ClassId.HeadClassId','created_by', 'updated_by']);
        $data['approval_request']=getNextApproval(43,auth()->user()->designation_id,$chartOfAccount->id);
        $data['approval_request_status']=checkApprovalRequestStatus(43,$chartOfAccount->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $this->authorizeAny([
            'chart_accounts_update',
            'manage_finance_configuration',
        ]);

        $request->validate([
            'name' => 'required',
            'code' => 'required|unique:chart_of_accounts,code,' . $chartOfAccount->id,
            //'account_type_id' => 'required',
            //'parent_id' => 'required',
            //'class_id' => 'required',
            //'description' => 'required',
        ]);
        if (isset($this->input['class_id'])) {
            $classes = $this->input['class_id'];
            unset($this->input['class_id']);
        }else{
            $classes=[];
        }
        try {
            DB::beginTransaction();
            $item = $chartOfAccount->update($this->input);

            if ($item){
                if ($classes){
                    foreach ($classes as $class){
                       ChartOfAccountClass::query()->updateOrCreate(['id' => $class['head_class_id']], $class);
                    }
                }
            }
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
    public function destroy(ChartOfAccount $chartOfAccount): JsonResponse
    {
        $this->authorizeAny([
            'chart_accounts_delete',
            'manage_finance_configuration'
        ]);

        $chartOfAccount->ClassId()->delete();
        $item = $chartOfAccount->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }




    public function financeDropdown(){
        $data['account_type']= Type::getTypeValues('account-type');
        $data['budget_type']= Type::getTypeValues('budget-type');
        $data['head_class']= HeadClass::query()->with('ProjectId')->where('status',1)->get();
        $data['payment_mode']= Type::getTypeValues('payment-mode');
        $data['payment_is']= Type::getTypeValues('payment-is');
        $data['voucher_type']= Type::getTypeValues('voucher-type');
        $data['currency']= Type::getTypeValues('currency');
        $data['employees']= Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['donors']= DonarProfile::all();
        $data['org_type']= Type::getTypeValues('org-type');
        $data['budget'] = ProjectBudget::with(['ProjectId','created_by', 'updated_by','BudgetDetail.Head' => ['parent','AccountTypeId']])->get();


        $data['implenting-partners'] = ProjectImplementingPartner::all();
        $data['nofo'] = Nofo::all();
        $data['financial_years'] = Type::getTypeValues('financial-years');
        $data['admin_bill_category'] = Type::getTypeValues('admin-bill-category');
        $data['banks'] = BankInfo::query()->with('HeadId')->get();
        $data['countries'] = Country::all();
        $data['coa'] = ChartOfAccount::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }


    public function taxDropdown(){
        $data['tax_types'] = Type::getTypeValues('tax-type');
        $data['tax_scope'] = Type::getTypeValues('tax-scope');
        $data['tax_computation'] = Type::getTypeValues('tax-computation');
        $data['tax_group'] = Type::getTypeValues('tax-group');
        $data['countries'] = Country::all();
        $data['provinces'] = Province::all();
        $data['coa'] = ChartOfAccount::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function budgetDropdown(){

        $data['project_budget_unit_type']= Type::getTypeValues('project-budget-unit-type');
        $data['heads']= ChartOfAccount::query()->with(['parent','ClassId'])->where('approval_status',1)->get();
        $data['projects']= $this->getProject();
        $data['program_budget_category']= Type::getTypeValues('budget-category');

        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    private function getProject()
    {
//        return ProjectProfile::with([
//            'projectGoals.projectOutcomes.projectOutputs.ProOutputIndicators.proWorkPlanIndicatorsActivites'
//        ])->where('approval_status',1)->get()->map(function ($project) {
//            $activities = collect();
//
//            foreach ($project->projectGoals as $goal) {
//                foreach ($goal->projectOutcomes as $outcome) {
//                    foreach ($outcome->projectOutputs as $output) {
//                        foreach ($output->proOutputIndicators as $indicator) {
//                            foreach ($indicator->proWorkPlanIndicatorsActivites as $activity) {
//                                $activities->push($activity);
//                            }
//                        }
//                    }
//                }
//            }
//            $project->makeHidden(['projectGoals']);
//            $project->activities = $activities;
//            return $project;
//        });

        $project = ProjectProfile::with('ProgressWorkPlanOutputByProjects.activities')->get();
        return $project;
    }

    public function sendCOAForApproval(ChartOfAccount $item)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $approval_process_name=ApprovalProcessName::query()->where('id',43)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',43)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',43)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            ChartOfAccount::query()->where('id',$item->id)->update($update);
            return resp(1,'Chart of Account sent for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Voucher approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
