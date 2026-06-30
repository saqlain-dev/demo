<?php

namespace App\Http\Controllers\Api\V1\StrategicPlan;

use App\Http\Controllers\Controller;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\AuctionGatePass;
use App\Models\Admin\ConsultantContract\ConsultantContract;
use App\Models\Admin\Fleet\FuelRequest;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Admin\GDN\Gdn;
use App\Models\Admin\Inventory;
use App\Models\Admin\InventoryReconciliation;
use App\Models\Admin\ItemVariant;
use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookReconciliation;
use App\Models\Admin\Library\BookRequest;
use App\Models\Admin\Procurement;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\StockRequest;
use App\Models\Admin\Tender;
use App\Models\Admin\VehicleMaintenanceForm;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;

use App\Models\Communication\CommunicationEvent;
use App\Models\Configuration\EmployeeStatusChange;
use App\Models\DisposeRequest;
use App\Models\EmailTemplate;
use App\Models\EmployeePayrollMaster;
use App\Models\EventManagement;
use App\Models\Customer;
use App\Models\EmployeeOffboarding;
use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Finance\Audit\AuditPlan;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtAdvocateExpense;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\Grants\GrantCloseOut;
use App\Models\Finance\Grants\GrantFinancialReport;
use App\Models\Finance\LasInvoice;
use App\Models\Finance\SubGrants\SubGrantCloseOut;
use App\Models\Finance\SubGrants\SubGrantFinancialReport;
use App\Models\Finance\Voucher\GeneralLedger;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\Voucher;
use App\Models\Governance\BoardMeeting;
use App\Models\Governance\BoardMeetingAgenda;
use App\Models\Governance\BoardResolutionPassed;
use App\Models\Governance\MinuteOfMeeting;
use App\Models\GratuityCalculation;
use App\Models\GRN;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\HR\AdvanceSalary\AdvanceSalaryInstallment;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Attendance\EmployeeManuelAttendance;
use App\Models\HR\Complaint\Complaint;
use App\Models\HR\Leaves\EmployeeLeave;
use App\Models\HR\Insurance\EmployeeInsurances;
use App\Models\HR\Payscale\SalaryRange;
use App\Models\HR\Policy;
use App\Models\HR\Recruitment\ConsultantTimesheet;
use App\Models\HR\Recruitment\EmployeeRequisition;
use App\Models\HR\Recruitment\OfferLetter;
use App\Models\HR\RetirementBenefit;
use App\Models\HR\TimeSheet\EmployeeTimesheet;
use App\Models\MeetingBooking;
use App\Models\Lead;
use App\Models\LeadQualification;
use App\Models\Opportunity\Opportunity;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\Rdu\ResearchMatrix;
use App\Models\Program\Rdu\RmPlan;
use App\Models\Program\ResultResourceFramework;
use App\Models\Progress\ProgressWorkplan;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Reimbursement;
use App\Models\SalaryIncrement;
use App\Models\Prospect;
use App\Models\Quotation\Quotation;
use App\Models\StrategicPlan;
use App\Models\StrategicPlanIndicator;
use App\Models\StrategicPlanIndicatorTarget;
use App\Models\StrategicPlanIndicatorYear;
use App\Models\StrategicPlanPillar;
use App\Models\Vendor;
use App\Models\VisitReimbursement;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isNull;

class StrategicPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'strategic_plan',
            'manage_audit_program_planning',
            'manage_audit_program_reports',
            'dashboard-program',
        ]);

        $data['strategicPlanList']=$strategicPlanList= StrategicPlan::with(['user','pillars.indicators'])->orderBy('id','desc')->get();

        $data['TotalCount']= $data['strategicPlanList']->count();
        $data['draftCount']= $data['strategicPlanList']->where('status',4)->count();
        $data['pendingCount']= $data['strategicPlanList']->where('status',2)->count();
        $data['approvedCount']= $data['strategicPlanList']->where('status',1)->count();
        $data['rejectedCount']= $data['strategicPlanList']->where('status',3)->count();

        $data['lasRrfDraftCount']= $data['strategicPlanList']->where('status',1)->where('las_rrf_approval',4)->count();
        $data['lasRrfPendingCount']= $data['strategicPlanList']->where('status',1)->where('las_rrf_approval',2)->count();
        $data['lasRrfApprovedCount']= $data['strategicPlanList']->where('status',1)->where('las_rrf_approval',1)->count();
        $data['lasRrfRejectedCount']= $data['strategicPlanList']->where('status',1)->where('las_rrf_approval',3)->count();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('sp_create');

        $request->validate([
            'name' => 'required',
            'from_year' => 'required',
            'to_year' => 'required',
        ]);
        $plan=StrategicPlan::query()->create($request->all());

        return resp(1,'Successful!', $plan,Response::HTTP_CREATED);
    }
    public function savePillars(Request $request){
        $this->authorize('sp_create');

        $request->validate([
            'sp_id' => 'required',
        ]);
        $plan=StrategicPlan::query()->findOrFail($request->sp_id);
        if($plan){
            $plan->pillars()->createMany($request->pillar);
        }
        $plan= StrategicPlan::with(['pillars.indicators' => ['indicatorYears']])->find($request->sp_id);
        return resp(1,'Successful!', $plan,Response::HTTP_CREATED);
    }
    public function updatePillars(Request $request,StrategicPlan $item){
        $this->authorize('sp_update');

        if($item){
            foreach($request->pillar as $pillar) {
                $pillar['strategic_plan_id']=$item->id;
               StrategicPlanPillar::query()->updateOrCreate(['id' => $pillar['id']], $pillar);
            }
        }
        $plan= StrategicPlan::with(['pillars.indicators' => ['indicatorYears']])->find($item->id);
        return resp(1,'Successful!', $plan,Response::HTTP_CREATED);
    }
    public function getPillars($item){
        $this->authorizeAny([
            'sp_view',
            'manage_audit_program_planning',
        ]);

        return StrategicPlan::with('pillars')->find($item);

    }

    public function deletePillar($id)
    {
        $this->authorize('sp_delete');

        $pillar=StrategicPlanPillar::query()->findOrFail($id);
        $pillar->indicators()->delete();
        $pillar->delete();
        $message='Pillar deleted successfully.';
        return resp(1,'Successful!', $message,Response::HTTP_OK);

    }

    public function saveIndicators(Request $request){
        $this->authorize('sp_create');

        $request->validate([
            'name' => 'required',
            'strategic_plan_pillar_id' => 'required',
            'status' => 'required',
        ]);
        $indicators=StrategicPlanIndicator::query()->create($request->all());
        if($indicators){
            //$indicators->indicatorTargets()->createMany($request->indicator_target);
            $indicators->indicatorYears()->createMany($request->indicator_year);
        }
        return resp(1,'Successful!', $indicators->toArray(),Response::HTTP_CREATED);
    }
    public function updateIndicators(Request $request,StrategicPlanIndicator $item){
        $this->authorize('sp_update');

        $request->validate([
            'name' => 'required',
            'status' => 'required',
        ]);
        try {

            DB::beginTransaction();
           $StrategicPlanIndicator= $item->update($request->all());

           if($StrategicPlanIndicator){
//               foreach ($request->indicator_target as $indicator_target){
//                   StrategicPlanIndicatorTarget::query()->updateOrCreate(['id' => $indicator_target['id']],$indicator_target);
//               }
               foreach ($request->indicator_year as $indicator_year){
                   StrategicPlanIndicatorYear::query()->updateOrCreate(['id' => $indicator_year['id']],$indicator_year);
               }
           }

            DB::commit();
            $data = StrategicPlanIndicator::with(['indicatorYears'])->find($item->id);
            return resp(1,'Successful!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }

        /*return $item;
        $request->validate([
            'name' => 'required',
            'status' => 'required',
        ]);
        $indicators=StrategicPlanIndicator::query()->update($request->all());
        if($indicators){
            $indicators->indicatorYears()->createMany($request->indicator_year);
        }
        return resp(1,'Successful!', $indicators->toArray(),Response::HTTP_CREATED);*/
    }

    public function deleteIndicator(StrategicPlanIndicator $item){
        $this->authorize('sp_delete');

        //$item->indicatorTargets()->delete();
        $item->indicatorYears()->delete();
        $item->delete();
        $message='Indicator deleted successfully.';
        return resp(1,'Successful!', $message,Response::HTTP_OK);
    }


    public function saveIndicatorYearActualValue(Request $request, StrategicPlanIndicatorYear $item){
        $this->authorize('sp_create');

        $request->validate([
            'actual' => 'required',
        ]);
        $item->actual=$request->actual;
        $item->save();
        return resp(1,'Successful!', $item->toArray(),Response::HTTP_CREATED);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorizeAny([
            'sp_view',
            'manage_audit_program_planning',
            'manage_audit_program_reports',
        ]);

        $data['responce']= StrategicPlan::with(['pillars.indicators' => ['indicatorYears'],'comments.createdBy'])->find($id);
        $data['approval_request']=getNextApproval(2,auth()->user()->designation_id,$id);
        $data['approval_request_status']=checkApprovalRequestStatus(2,$id);


        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('sp_update');

        $request->validate([
            'name' => 'required',
            'from_year' => 'required',
            'to_year' => 'required',

        ]);

        try {
            DB::beginTransaction();


            $item = StrategicPlan::query()->findOrFail($id);
            $item->update($request->only(['name', 'from_year','to_year']));


            foreach($request->pillars as $piller){
                StrategicPlanPillar::query()->updateOrCreate(['id' => $piller['id']],$piller);
                foreach ($piller['indicators'] as $indicator){
                    StrategicPlanIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
//                    foreach ($indicator['indicator_targets'] as $indicator_target){
//                        StrategicPlanIndicatorTarget::query()->updateOrCreate(['id' => $indicator_target['id']],$indicator_target);
//                    }

                    foreach ($indicator['indicator_years'] as $indicator_year){
                        StrategicPlanIndicatorYear::query()->updateOrCreate(['id' => $indicator_year['id']],$indicator_year);
                    }
                }
            }

            DB::commit();
            $data = StrategicPlan::with(['pillars.indicators' => ['indicatorYears']])->find($id);
            return resp(1,'Successful!', $data,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            return resp(0,'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()],Response::HTTP_EXPECTATION_FAILED);
        }

    }
    public function updateSP(Request $request, $id){
        $this->authorize('sp_update');

        $request->validate([
            'name' => 'required'
        ]);
        StrategicPlan::query()->where('id',$id)->update($request->all());
        $responce= StrategicPlan::query()->with('user')->findOrFail($id);
        return resp(1,'Successful!', $responce,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('sp_delete');

        $plan = StrategicPlan::query()->findOrFail($id);
        if ($plan->status == \STATUS::APPROVED)
            return resp(1,'Unsuccessful!', [],Response::HTTP_OK);

        $plan->delete();
        return resp(1,'Successful!', [],Response::HTTP_OK);
    }


    public function getSpIndicatorBySpId($strategicPlanId)
    {
        $this->authorizeAny([
            'sp_view',
            'manage_audit_program_planning',
        ]);

        $strategicPlan = StrategicPlan::with(['pillars.indicators' => ['indicatorYears']])->findOrFail($strategicPlanId);
        $indicators = $strategicPlan->pillars->flatMap->indicators;
        return resp(1, 'Successful!', $indicators, Response::HTTP_CREATED);
    }

    public function deleteYears(StrategicPlan $item)
    {
        $this->authorize('sp_delete');

        $item->indicators()->delete();
        return resp(1,'Successful!', [],Response::HTTP_OK);

    }

    public function getDdList()
    {
        // $this->authorize('sp_veiw');

        $data = StrategicPlan::query()->get(['id','name','status','las_rrf_approval']);
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('sp_update');

        $request->validate(['id','status']);
        $item = StrategicPlan::query()->findOrFail($request->id)->update($request->only(['status']));
        return resp(1,'Successful!', $item,Response::HTTP_OK);
    }

    public function sendForApproval(StrategicPlan $item)
    {
        $this->authorize('sp_update');
        $approval_process_name=ApprovalProcessName::query()->where('id',2)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',2)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',2)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($sp_approval_process->count() > 0 && $checkProcess == 0){

            foreach ($sp_approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);
                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);

            }
            $update=array('status'=>2);
            StrategicPlan::query()->where('id',$item->id)->update($update);
            return resp(1,'Strategic Plan send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Strategic Plan approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function updateRequest(Request $request,ApprovalProcessList $item)
    {

        $item->approval_status=$request->approval_status;
        $item->comments=$request->comments;
        $item->save();
        if($request->approval_status == 3){
            $updateProcess=array(
                "approval_request_status"=>0
            );
            ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->update($updateProcess);
        }
        $approval_process_name=ApprovalProcessName::query()->where('id',$item->approval_process_id)->first();


        if($item->approval_process_id == 1){

            if($request->approval_status == 3){

                EmployeeLeave::query()->where('id',$item->request_module_id)->update(array('approval_status'=>3));
                reverseDeductLeave($item->request_module_id);
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeLeave::query()->where('id',$item->request_module_id)->update(array('approval_status'=>1));
                    //deductLeave($item->request_module_id);
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if($item->approval_process_id == 2){

            if($request->approval_status == 3){

                StrategicPlan::query()->where('id',$item->request_module_id)->update(array('status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    StrategicPlan::query()->where('id',$item->request_module_id)->update(array('status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 3){

            if($request->approval_status == 3){

                StrategicPlan::query()->where('id',$item->request_module_id)->update(array('las_rrf_approval'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    StrategicPlan::query()->where('id',$item->request_module_id)->update(array('las_rrf_approval'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 4){

            if($request->approval_status == 3){

                ProjectProfile::query()->where('id',$item->request_module_id)->update(array('approval_status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ProjectProfile::query()->where('id',$item->request_module_id)->update(array('approval_status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 6){

            if($request->approval_status == 3){

                ProjectProfile::query()->where('id',$item->request_module_id)->update(array('project_rrf_approval'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ProjectProfile::query()->where('id',$item->request_module_id)->update(array('project_rrf_approval'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 7){

            if($request->approval_status == 3){

                ProgressWorkplan::query()->where('id',$item->request_module_id)->update(array('status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ProgressWorkplan::query()->where('id',$item->request_module_id)->update(array('status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 8){

            if($request->approval_status == 3){

                PurchaseRequest::query()->where('id',$item->request_module_id)->update(array('pr_approval_status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    PurchaseRequest::query()->where('id',$item->request_module_id)->update(array('pr_approval_status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 9){

            if($request->approval_status == 3){

                PurchaseRequestRfq::query()->where('id',$item->request_module_id)->update(array('status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    PurchaseRequestRfq::query()->where('id',$item->request_module_id)->update(array('status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 5){

            if($request->approval_status == 3){

                ProjectMnePlan::query()->where('id',$item->request_module_id)->update(array('approval_status'=>3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ProjectMnePlan::query()->where('id',$item->request_module_id)->update(array('approval_status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if($item->approval_process_id == 11){

            if($request->approval_status == 3){

                Tender::query()->where('id',$item->request_module_id)->update(array('approval_status'=>3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
            }else{

                $CheckNextApprovalRecord=ApprovalProcessList::query()->where('approval_process_id',$item->approval_process_id)->where('request_module_id',$item->request_module_id)->where('process_order',$item->process_order + 1 )->where('approval_status',2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Tender::query()->where('id',$item->request_module_id)->update(array('approval_status'=>1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if ($item->approval_process_id == 12) {

            if ($request->approval_status == 3) {

                Inventory::query()->where('id', $item->request_module_id)->update(array('idle_approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Inventory::query()->where('id', $item->request_module_id)->update(array('idle_approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }
        if ($item->approval_process_id == 13) {

            if ($request->approval_status == 3) {

                Procurement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();
                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {
                    Procurement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }

        if ($item->approval_process_id == 14) {

            if ($request->approval_status == 3) {

                EmployeeInsurances::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeInsurances::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }

       if ($item->approval_process_id == 15) {

            if ($request->approval_status == 3) {

                EmployeeLeave::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                reverseDeductLeave($item->request_module_id);
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeLeave::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    //deductLeave($item->request_module_id);
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }

       if ($item->approval_process_id == 16) {

            if ($request->approval_status == 3) {

                VehicleMaintenanceForm::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    VehicleMaintenanceForm::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 17) {

            if ($request->approval_status == 3) {

                VehicleRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    VehicleRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 18) {

            if ($request->approval_status == 3) {

                AirTravelRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AirTravelRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 19) {

            if ($request->approval_status == 3) {

                BookRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    BookRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 20) {

            if ($request->approval_status == 3) {

                Complaint::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Complaint::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 21) {

            if ($request->approval_status == 3) {

                ResearchMatrix::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ResearchMatrix::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 22) {

            if ($request->approval_status == 3) {

                RmPlan::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    RmPlan::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 23) {

            if ($request->approval_status == 3) {

                Policy::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Policy::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }


        if ($item->approval_process_id == 24) {

            if ($request->approval_status == 3) {

                AdvanceSalary::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AdvanceSalary::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    AdvanceSalaryInstallment::query()->where('advance_salary_id', $item->request_module_id)->update(array('status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 25) {

            if ($request->approval_status == 3) {

                BoardResolutionPassed::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    BoardResolutionPassed::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 26) {

            if ($request->approval_status == 3) {

                BoardMeeting::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    BoardMeeting::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 27) {

            if ($request->approval_status == 3) {

                BoardMeetingAgenda::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    BoardMeetingAgenda::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 28) {

            if ($request->approval_status == 3) {

                MinuteOfMeeting::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    MinuteOfMeeting::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 29) {

            if ($request->approval_status == 3) {

                ItemVariant::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ItemVariant::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 30) {

            if ($request->approval_status == 3) {

                EmployeeRequisition::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeRequisition::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 31) {

            if ($request->approval_status == 3) {

                EmployeePayrollMaster::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeePayrollMaster::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 32) {

            if ($request->approval_status == 3) {

                PerformancePlanning::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    PerformancePlanning::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 33) {

            if ($request->approval_status == 3) {

                EmployeeStatusChange::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeStatusChange::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 34) {

            if ($request->approval_status == 3) {

                BookReconciliation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    BookReconciliation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 35) {

            if ($request->approval_status == 3) {

                InventoryReconciliation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    InventoryReconciliation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 36) {

            if ($request->approval_status == 3) {

                GRN::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    GRN::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    $this->approveGrn($item->request_module_id);

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }

       if ($item->approval_process_id == 37) {

            if ($request->approval_status == 3) {

                FuelRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    FuelRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 38) {

            if ($request->approval_status == 3) {

                ProjectBudget::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ProjectBudget::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

       if ($item->approval_process_id == 39) {

            if ($request->approval_status == 3) {

                AnnualBudget::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AnnualBudget::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
       if ($item->approval_process_id == 40) {

            if ($request->approval_status == 3) {

                SalaryRange::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    SalaryRange::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
       if ($item->approval_process_id == 41) {

            if ($request->approval_status == 3) {

                Voucher::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Voucher::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    $voucherDetail=Voucher::query()->where('id', $item->request_module_id)->first();
                    if ($voucherDetail->VoucherType == 'JV')
                    {
                        Voucher::query()->where('id', $voucherDetail->id)->update(array('IsPosted' => 1));
                        GeneralLedger::query()->where('voucher_no', $voucherDetail->id)->update(array('IsPosted' => 1));
                    }

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);


                }

            }

        }
       if ($item->approval_process_id == 42) {

            if ($request->approval_status == 3) {

                AuditPlan::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AuditPlan::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 43) {

            if ($request->approval_status == 3) {

                ChartOfAccount::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ChartOfAccount::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 44) {

            if ($request->approval_status == 3) {

                CommunicationEvent::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    CommunicationEvent::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 45) {

            if ($request->approval_status == 3) {

                RetirementBenefit::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    RetirementBenefit::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 46) {

            if ($request->approval_status == 3) {

                EmployeeTimesheet::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeTimesheet::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 47) {

            if ($request->approval_status == 3) {

                AuctionGatePass::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AuctionGatePass::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 48) {

            if ($request->approval_status == 3) {

                StockRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    StockRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 49) {

            if ($request->approval_status == 3) {

                EmployeeOffboarding::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeOffboarding::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 50) {

            if ($request->approval_status == 3) {

                AdminInvoice::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    AdminInvoice::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 51) {

            if ($request->approval_status == 3) {

                Reimbursement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Reimbursement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 52) {

            if ($request->approval_status == 3) {

                ClaimTravelExpense::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ClaimTravelExpense::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 53) {

            if ($request->approval_status == 3) {

                CourtAdvocateExpense::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    CourtAdvocateExpense::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 54) {

            if ($request->approval_status == 3) {

                EmployeeManuelAttendance::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeManuelAttendance::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    addAttendance($item->request_module_id);

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
                }

            }

        }

        if ($item->approval_process_id == 55) {

            if ($request->approval_status == 3) {

                ConsultantTimesheet::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ConsultantTimesheet::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 56) {

            if ($request->approval_status == 3) {

                GratuityCalculation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    GratuityCalculation::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 57) {

            if ($request->approval_status == 3) {

                LasInvoice::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    LasInvoice::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 58) {

            if ($request->approval_status == 3) {

                OfferLetter::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    OfferLetter::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 59) {

            if ($request->approval_status == 3) {

                EmployeeRequisition::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmployeeRequisition::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        if ($item->approval_process_id == 60) {

            if ($request->approval_status == 3) {

                JournalVoucher::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    JournalVoucher::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    GeneralLedger::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }



            }

        }
        if ($item->approval_process_id == 61) {

            if ($request->approval_status == 3) {

                GrantCloseOut::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    GrantCloseOut::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 62) {

            if ($request->approval_status == 3) {

                SubGrantCloseOut::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    SubGrantCloseOut::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 63) {

            if ($request->approval_status == 3) {

                GrantFinancialReport::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));

                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    GrantFinancialReport::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 64) {

            if ($request->approval_status == 3) {

                SubGrantFinancialReport::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    SubGrantFinancialReport::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 65) {

            if ($request->approval_status == 3) {

                Vendor::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);
            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    Vendor::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));
                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 66) {

            if ($request->approval_status == 3) {

                PurchaseOrder::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    PurchaseOrder::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 67) {

            if ($request->approval_status == 3) {

                WorkOrder::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    WorkOrder::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 68) {

            if ($request->approval_status == 3) {

                WorkCompletion::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    WorkCompletion::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 69) {

            if ($request->approval_status == 3) {

                ConsultantContract::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    ConsultantContract::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 70) {

            if ($request->approval_status == 3) {

                DisposeRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    DisposeRequest::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 71) {

            if ($request->approval_status == 3) {

                EmailTemplate::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EmailTemplate::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 72) {

            if ($request->approval_status == 3) {

                VisitReimbursement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    VisitReimbursement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 73) {

            if ($request->approval_status == 3) {

                MeetingBooking::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    MeetingBooking::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 75) {

            if ($request->approval_status == 3) {

                EventManagement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    EventManagement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }
        if ($item->approval_process_id == 76) {

            if ($request->approval_status == 3) {

                SalaryIncrement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 3));
                sendFinalRejectionNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

            } else {

                $CheckNextApprovalRecord = ApprovalProcessList::query()->where('approval_process_id', $item->approval_process_id)->where('request_module_id', $item->request_module_id)->where('process_order', $item->process_order + 1)->where('approval_status', 2)->first();

                if (isNull($CheckNextApprovalRecord) && empty($CheckNextApprovalRecord)) {

                    SalaryIncrement::query()->where('id', $item->request_module_id)->update(array('approval_status' => 1));

                    sendFinalApprovalNotification($item->designation_id,$approval_process_name->approval_process_name,$item->created_by);

                }

            }

        }

        return resp(1,'Request updated successfully', [],Response::HTTP_OK);
    }

    public function checkRequestStatus(Request $request)
    {
        $this->authorize('sp_view');

        $data['request_status']=ApprovalProcessList::query()->where('approval_process_id',$request->approval_process_id)->where('request_module_id',$request->request_module_id)->with('designation')->orderBy('process_order','ASC')->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    public function approveGrn($grn_id)
    {
        try {
            DB::beginTransaction();

            $grn=GRN::query()->findOrFail($grn_id);
            $grnItems=$grn->load('grnItem','poDetails')->toArray();
            if(!empty($grn) && $grn->approval_status == 1){

                foreach($grnItems['grn_item'] as $items){
                    $statement = DB::select("SELECT IDENT_CURRENT('inventories') as nextID");
                    $inventoryNO='IN/'.sprintf('%04d', $statement[0]->nextID);
                   // $this->input['inventory_no']=$inventoryNO;
                    $inventory = Inventory::query()->firstOrCreate(
                        ['item_id' => $items['item_id']],
                        [
                            "inventory_no"=>$inventoryNO,
                            "item_id"=>$items['item_id'],
                            "quantity"=>$items['required_quantity'],
                            "initial_quantity"=>$items['required_quantity'],
                            "purchase_date"=>date('Y-m-d',strtotime($grnItems['po_details']['purchase_order_date'])),
                            "po_id"=>$grnItems['po_details']['id'],
                        ]
                    );

                    $quantity = $inventory->quantity;
                    if(optional(optional($inventory->item)->item_type)->id!='144'){
                        $quantity=1;
                    }
                    for ($i = 0; $i < $quantity; $i++) {
                        $statement = DB::select("SELECT IDENT_CURRENT('item_variants') as nextID");
                        $serialNo ='IV/'.sprintf('%04d', $statement[0]->nextID);

                        ItemVariant::query()->create([
                            'serial_no' => $serialNo,
                            'item_id' => $inventory->item_id,
                            'inventory_id' => $inventory->id,
                            "purchase_date"=>date('Y-m-d',strtotime($grnItems['po_details']['purchase_order_date'])),
                            "po_id"=>$grnItems['po_details']['id'],
                            "cost"=>$grnItems['grn_item']['unit_price'] ?? NULL,
                        ]);
                    }
                }
            }

            DB::commit();

           // return resp('1', 'Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
           // DB::rollBack();
            //return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
