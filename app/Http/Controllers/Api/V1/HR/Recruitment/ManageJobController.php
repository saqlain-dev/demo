<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Models\Configuration\GeneralTemplates;
use App\Models\Designation;
use App\Models\District;
use App\Models\HR\Appraisal\DepartmentalObjective;
use App\Models\HR\Recruitment\CandidateHistory;
use App\Models\HR\Recruitment\CandidateOnlineTest;
use App\Models\HR\Recruitment\InterviewQuestion;
use App\Models\HR\Recruitment\QuestionAnswer;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use App\Models\Employee;
use App\Models\HeadOffice;
use App\Models\BranchOffice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Admin\Library\Book;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\ApplyJob;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\HR\Appraisal\AppriasalKpi;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\EmployeeRequisition;

class ManageJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'jobs_view',
            'rfp_view',
            'manage_audit_recruitment',
        ]);

        $data = ManageJob::with(['DepartmentId','RequiredJobType','RequisitionId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function viewJobs()
    {
        // $this->authorize('jobs_view');

        $data = ManageJob::with(['DepartmentId','RequiredJobType','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'jobs_create',
            'rfp_create',
        ]);

        $request->validate([
            'job_title' => 'required',
            //'department_id' => 'required',
            //'job_location' => 'required',
            //'no_of_vacancies' => 'required',
            //'experience' => 'required',
            //'salary' => 'required',
            //'required_job_type' => 'required',
            'status' => 'required',
            //'responsibilities' => 'required',
            'deadline' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ManageJob::query()->create($this->input);
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
    public function show(ManageJob $manageJob): JsonResponse
    {
        $this->authorizeAny([
            'jobs_view',
            'rfp_view',
            'manage_audit_recruitment',
        ]);

        $manageJob = $manageJob->load(['DepartmentId','RequiredJobType','RequisitionId','created_by','updated_by']);
        return resp('1', 'Successful!', $manageJob, Response::HTTP_OK);
    }
    public function getJobDetail($id): JsonResponse
    {
        // $this->authorize('jobs_view');

        $manageJob = ManageJob::query()->findOrFail($id);
        $manageJob = $manageJob->load(['DepartmentId','RequiredJobType','RequisitionId','created_by','updated_by']);
        return resp('1', 'Successful!', $manageJob, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ManageJob $manageJob)
    {
        $this->authorizeAny([
            'jobs_update',
            'rfp_update',
        ]);

        $request->validate([
            'job_title' => 'required',
            //'department_id' => 'required',
            //'job_location' => 'required',
            //'no_of_vacancies' => 'required',
            //'experience' => 'required',
            //'salary' => 'required',
            //'required_job_type' => 'required',
            'status' => 'required',
            //'responsibilities' => 'required',
            'deadline' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $manageJob->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function applyOnJob(Request $request)
    {
        $request->validate([
            'job_id' => 'required',
            //'candidate_name' => 'required',
            //'candidate_cnic' => 'required',
            //'candidate_email' => 'required',
            //'candidate_phone' => 'required',
            //'candidate_gender' => 'required',
            //'current_location' => 'required',
            //'currently_employed' => 'required',
            // 'currently_salary' => 'required',
            //'expected_salary' => 'required',
            // 'current_company' => 'required',
            //'candidate_resume' => 'required',
            //'expected_joining_date' => 'date',
        ]);

        if($request->hasFile('candidate_resume')) {
            $responce = $this->saveCandidateResume($request, 'candidate_resume');
            if ($responce) {
                $this->input['candidate_resume'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = ApplyJob::query()->create($this->input);
            DB::commit();
            return resp('1', 'Applied Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateApplyJob(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'job_id' => 'required',
            //'candidate_cnic' => 'required',
            //'candidate_email' => 'required',
            //'candidate_phone' => 'required',
            //'candidate_gender' => 'required',
            //'current_location' => 'required',
            //'currently_employed' => 'required',
            // 'currently_salary' => 'required',
            //'expected_salary' => 'required',
            // 'current_company' => 'required',
            //'candidate_resume' => 'required',
            //'expected_joining_date' => 'date',
        ]);
        $appliedJob = ApplyJob::query()->findOrFail($this->input['id']);
        if($request->hasFile('candidate_resume')) {
            $responce = $this->saveCandidateResume($request, 'candidate_resume');
            if ($responce) {
                $appliedJob->update(['candidate_resume'=> $responce]);
            }
        }
        try {
            DB::beginTransaction();
            $item = $appliedJob->update($request->except(['id','job_id']));
            DB::commit();
            return resp('1', 'Candidate updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveCandidateResume($request,$folder){

        $file = $request->file('candidate_resume');
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

    public function viewAppliedJobs($job_id)
    {
        $data['interviewPanel'] = InterviewCommittee::query()->with('InterviewCommitteeMembers.EmployeeId.designation')->where('apply_job_id',$job_id)->get();
        $data['appliedJobs'] = ApplyJob::with(['comments','JobId','ScheduledInterviews','candidateOnlineTest.questionnaireForm','questionnaires.answers.question','PanelistAnswers' => ['EmployeeId','InterviewQuestionId','QuestionOptionId'], 'OfferLetter','PoolBucketType'])->where('job_id',$job_id)->get();
        $data['InterviewQuestions'] = InterviewQuestion::query()->with('QOptions')->get();
        $data['JobDetail'] = ManageJob::query()->with('comments.createdBy')->findOrFail($job_id);
        if (isset($data['appliedJobs']->candidateOnlineTest)){
            $data['SubmittedTestAnswers'] = CandidateOnlineTest::query()->with('questionnaireForm', 'applyJob.questionnaires.answers.question')->where('uuid', $data['appliedJobs']->candidateOnlineTest?->uuid)->first();
        }
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function listAllCandidates()
    {
        $data['list'] = ApplyJob::with(['JobId.RequisitionId','PoolBucketType'])->get();
        $data['pool_buckets_types'] = Type::getTypeValues('pool-buckets-type');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getAppliedJob($id): JsonResponse
    {
        $applyJob = ApplyJob::query()->with(['interviewResults','JobId','InterviewPanel.InterviewCommitteeMembers.EmployeeId','ScheduledInterviews','candidateOnlineTest.questionnaireForm','questionnaires.answers.question', 'CandidateHistory', 'OfferLetter'])->findOrFail($id);
        $data['interviewPanel'] = InterviewCommittee::query()->with('InterviewCommitteeMembers.EmployeeId.designation')->where('apply_job_id',$applyJob->job_id)->get();
        $questionAnswers = QuestionAnswer::query()->with(['ApplyJobId','EmployeeId','InterviewQuestionId','QuestionOptionId'])->where('job_id',$applyJob->job_id)->get();
        $data['AllPanelAnswers'] = $questionAnswers->groupBy(function($item) {
            //dd($item->EmployeeId->name);
            return $item->EmployeeId->name;
        });
        $data['applyJob'] = $applyJob;
        $data['SubmittedTestAnswers'] = CandidateOnlineTest::query()->with('questionnaireForm', 'applyJob.questionnaires.answers.question')->where('uuid', $applyJob->candidateOnlineTest?->uuid)->first();
        $data['InterviewQuestions'] = InterviewQuestion::query()->with('QOptions')->get();

        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function changeCandidateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'negotiated_salary' => 'nullable',
            'status' => 'nullable',
            'pool_bucket_type' => 'nullable',
        ]);
        try {
            $id = $this->input['id'];
            unset($this->input['id']);
            $job = ApplyJob::query()->findOrFail($id);
            DB::beginTransaction();
            $item = $job->update($this->input);

            if ($item && !empty($request->status)){
                $status = $this->input['status'];
                $history = array(
                    'apply_job_id' => $id,
                    'old_status' => $status,
                    'new_status' => $this->input['status'],
                    'date' => date('Y-m-d')
                );
                CandidateHistory::query()->create($history);
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
    public function destroy(ManageJob $manageJob): JsonResponse
    {
        $this->authorizeAny([
            'jobs_delete',
            'rfp_delete',
        ]);

        $item = $manageJob->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
    public function hrDropdown(){
        $data['letter_templates'] = GeneralTemplates::all();
        $data['department']= Type::getTypeValues('department-names');
        $data['domains']= Type::getTypeValues('domains');
        $data['pool_buckets_type']= Type::getTypeValues('pool-buckets-type');
        $data['designations']= Designation::all();
        $data['districts']= District::all();
        $data['head_office']= HeadOffice::all();
        $data['branch_office']= BranchOffice::all();
        $data['employeeType']= Type::getTypeValues('employee-type');
        $data['required_contract_type']= Type::getTypeValues('required-contract-type');
        $data['employee_contract_type']= Type::getTypeValues('employee-contract-type');
        $data['required_job_type']= Type::getTypeValues('required-job-type');
        $data['job_requisitions']= EmployeeRequisition::query()->where('status',1)->get();
        $data['job_mode']= Type::getTypeValues('job-mode');
        $data['employees']= Employee::with(['EmployeeSalary','employeeTyp'])->get();
        $data['gender']=Type::getTypeValues('employee-gender');
        foreach ($data['employees'] as $project){
            $project->ProjectDetail = $project->project_details;
        }
        $data['retired_employees'] = Employee::with(['EmployeeSalary','employeeTyp'])
            ->whereNotNull('leave_date')
            ->get();
        $statement = DB::select("SELECT IDENT_CURRENT('employee_requisitions') as nextID");
        $data['requisition_serial_no'] = sprintf('%04d', $statement[0]->nextID) ?? '0001';
        $data['kpis'] = AppriasalKpi::query()->with(['DesignationId','KpiIndicators'])->get();
        $data['projects']= ProjectProfile::query()->where(['approval_status'=>1])->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
