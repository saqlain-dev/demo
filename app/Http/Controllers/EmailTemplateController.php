<?php

namespace App\Http\Controllers;

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Designation;
use App\Models\District;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\HR\Recruitment\ApplyJob;
use App\Models\TypeValue;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['items'] = EmailTemplate::with(['TemplateType','created_by','updated_by','approvalProcess'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            //'employee_id' => 'required',
            'template_name' => 'required',
            'template_subject' => 'required',
            'template_body' => 'required',
            'template_key' => 'required',
            'template_type' => 'required',
        ]);

        try {
            DB::beginTransaction();
            // Employee Details
            //$employee_id = $this->input['employee_id'];
            $letterContent = $this->input['template_body'];
            //$employee = Employee::query()->findOrFail($employee_id);

//            /*$employeeName = $employee->name;
//            $employeeNo = $employee->employee_no;
//            $dateOfBirth = $employee->date_of_birth;
//            $district = District::query()->select('name')->find($employee->district_id)->name ?? 'N/A';
//            $employeeType = TypeValue::query()->select('name')->find($employee->employee_type)->name ?? 'N/A';
//            $leaveDate = $employee->leave_date;
//            $cnic = $employee->cnic;
//            $phoneNo = $employee->phone_no;
//            $cnicIssuance = $employee->cnic_issuance;
//            $cnicExpiry = $employee->cnic_expiry;
//            $personalEmail = $employee->personal_email;
//            $officialEmail = $employee->offical_email;
//            $department = TypeValue::query()->select('name')->find($employee->department_id)->name ?? 'N/A';
//            $reportTo = Employee::query()->select('name')->find($employee->report_to_id)->name ?? 'N/A';
//            $bloodGroup = TypeValue::query()->select('name')->find($employee->blood_group)->name ?? 'N/A';
//            $dateOfJoining = $employee->date_of_joining;
//            $designation = Designation::query()->select('name')->find($employee->designation_id)->name ?? 'N/A';
//            $residentialAddress = $employee->residential_address;
//            $permanentAddress = $employee->permanent_address;*/

            // Additional dynamic placeholders
            $currentDate = now()->format('Y-m-d'); // Current date
            $currentTime = now()->format('H:i:s'); // Current time
            $currentDateTime = now()->toDateTimeString(); // Full date-time
            $currentYear = now()->year; // Current year
            $currentMonth = now()->format('F'); // Current month name

            // Replace variables with actual values
//            $letterContent = str_replace('{{EmployeeName}}', $employeeName, $letterContent);
//            $letterContent = str_replace('{{EmployeeNo}}', $employeeNo, $letterContent);
//            $letterContent = str_replace('{{DateOfBirth}}', $dateOfBirth, $letterContent);
//            $letterContent = str_replace('{{District}}', $district, $letterContent);
//            $letterContent = str_replace('{{EmployeeType}}', $employeeType, $letterContent);
//            $letterContent = str_replace('{{LeaveDate}}', $leaveDate, $letterContent);
//            $letterContent = str_replace('{{Cnic}}', $cnic, $letterContent);
//            $letterContent = str_replace('{{PhoneNo}}', $phoneNo, $letterContent);
//            $letterContent = str_replace('{{CnicIssuance}}', $cnicIssuance, $letterContent);
//            $letterContent = str_replace('{{CnicExpiry}}', $cnicExpiry, $letterContent);
//            $letterContent = str_replace('{{PersonalEmail}}', $personalEmail, $letterContent);
//            $letterContent = str_replace('{{OfficialEmail}}', $officialEmail, $letterContent);
//            $letterContent = str_replace('{{Department}}', $department, $letterContent);
//            $letterContent = str_replace('{{ReportTo}}', $reportTo, $letterContent);
//            $letterContent = str_replace('{{BloodGroup}}', $bloodGroup, $letterContent);
//            $letterContent = str_replace('{{DateOfJoining}}', $dateOfJoining, $letterContent);
//            $letterContent = str_replace('{{Designation}}', $designation, $letterContent);
//            $letterContent = str_replace('{{ResidentialAddress}}', $residentialAddress, $letterContent);
//            $letterContent = str_replace('{{PermanentAddress}}', $permanentAddress, $letterContent);

            // Replace additional placeholders
            $letterContent = str_replace('{{CurrentDate}}', $currentDate, $letterContent);
            $letterContent = str_replace('{{CurrentTime}}', $currentTime, $letterContent);
            $letterContent = str_replace('{{CurrentDateTime}}', $currentDateTime, $letterContent);
            $letterContent = str_replace('{{CurrentYear}}', $currentYear, $letterContent);
            $letterContent = str_replace('{{CurrentMonth}}', $currentMonth, $letterContent);

            // Assign updated template body back to input
            $this->input['template_body'] = $letterContent;

            // Create the email template
            $data['item'] = EmailTemplate::query()->create($this->input);




            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        $data['item'] = $emailTemplate->load(['TemplateType','created_by','updated_by','approvalProcess']);

        $data['approval_request'] = getNextApproval(71, auth()->user()->designation_id, $emailTemplate->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(71, $emailTemplate->id);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $emailTemplates = EmailTemplate::query()->findOrFail($id);
        $request->validate([
            'template_name' => 'required',
            'template_subject' => 'required',
            'template_body' => 'required',
            'template_key' => 'required',
            'template_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            // Employee Details
            //$employee_id = $this->input['employee_id'];
            $letterContent = $this->input['template_body'];
            //$employee = Employee::query()->findOrFail($employee_id);

//            $employeeName = $employee->name;
//            $employeeNo = $employee->employee_no;
//            $dateOfBirth = $employee->date_of_birth;
//            $district = District::query()->select('name')->find($employee->district_id)->name ?? 'N/A';
//            $employeeType = TypeValue::query()->select('name')->find($employee->employee_type)->name ?? 'N/A';
//            $leaveDate = $employee->leave_date;
//            $cnic = $employee->cnic;
//            $phoneNo = $employee->phone_no;
//            $cnicIssuance = $employee->cnic_issuance;
//            $cnicExpiry = $employee->cnic_expiry;
//            $personalEmail = $employee->personal_email;
//            $officialEmail = $employee->offical_email;
//            $department = TypeValue::query()->select('name')->find($employee->department_id)->name ?? 'N/A';
//            $reportTo = Employee::query()->select('name')->find($employee->report_to_id)->name ?? 'N/A';
//            $bloodGroup = TypeValue::query()->select('name')->find($employee->blood_group)->name ?? 'N/A';
//            $dateOfJoining = $employee->date_of_joining;
//            $designation = Designation::query()->select('name')->find($employee->designation_id)->name ?? 'N/A';
//            $residentialAddress = $employee->residential_address;
//            $permanentAddress = $employee->permanent_address;

            // Additional dynamic placeholders
            $currentDate = now()->format('Y-m-d'); // Current date
            $currentTime = now()->format('H:i:s'); // Current time
            $currentDateTime = now()->toDateTimeString(); // Full date-time
            $currentYear = now()->year; // Current year
            $currentMonth = now()->format('F'); // Current month name

            // Replace variables with actual values
//            $letterContent = str_replace('{{EmployeeName}}', $employeeName, $letterContent);
//            $letterContent = str_replace('{{EmployeeNo}}', $employeeNo, $letterContent);
//            $letterContent = str_replace('{{DateOfBirth}}', $dateOfBirth, $letterContent);
//            $letterContent = str_replace('{{District}}', $district, $letterContent);
//            $letterContent = str_replace('{{EmployeeType}}', $employeeType, $letterContent);
//            $letterContent = str_replace('{{LeaveDate}}', $leaveDate, $letterContent);
//            $letterContent = str_replace('{{Cnic}}', $cnic, $letterContent);
//            $letterContent = str_replace('{{PhoneNo}}', $phoneNo, $letterContent);
//            $letterContent = str_replace('{{CnicIssuance}}', $cnicIssuance, $letterContent);
//            $letterContent = str_replace('{{CnicExpiry}}', $cnicExpiry, $letterContent);
//            $letterContent = str_replace('{{PersonalEmail}}', $personalEmail, $letterContent);
//            $letterContent = str_replace('{{OfficialEmail}}', $officialEmail, $letterContent);
//            $letterContent = str_replace('{{Department}}', $department, $letterContent);
//            $letterContent = str_replace('{{ReportTo}}', $reportTo, $letterContent);
//            $letterContent = str_replace('{{BloodGroup}}', $bloodGroup, $letterContent);
//            $letterContent = str_replace('{{DateOfJoining}}', $dateOfJoining, $letterContent);
//            $letterContent = str_replace('{{Designation}}', $designation, $letterContent);
//            $letterContent = str_replace('{{ResidentialAddress}}', $residentialAddress, $letterContent);
//            $letterContent = str_replace('{{PermanentAddress}}', $permanentAddress, $letterContent);

            // Replace additional placeholders
            $letterContent = str_replace('{{CurrentDate}}', $currentDate, $letterContent);
            $letterContent = str_replace('{{CurrentTime}}', $currentTime, $letterContent);
            $letterContent = str_replace('{{CurrentDateTime}}', $currentDateTime, $letterContent);
            $letterContent = str_replace('{{CurrentYear}}', $currentYear, $letterContent);
            $letterContent = str_replace('{{CurrentMonth}}', $currentMonth, $letterContent);

            // Assign updated template body back to input
            $this->input['template_body'] = $letterContent;
            $data['item'] = $emailTemplates->update($this->input);


            DB::commit();
            return resp('1', 'Record updated Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $data['item'] = $emailTemplate->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }

    public function getEmailTemplateContent(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'candidate_id' => 'nullable|integer|exists:apply_jobs,id',
        ]);
        $template_id = $this->input['template_id'];
        $template = EmailTemplate::findOrFail($template_id);
        $letterContent = $template->template_data;
//        if ($request->filled('vendor_id')) {
//            $vendor = Vendor::findOrFail($this->input['vendor_id']);
//            $vendorFields = [
//                'company_name', 'contact_person_1', 'address_1', 'email_address',
//                'telephone_1', 'fax_no', 'main_area_of_business', 'other_area_of_business',
//                'year_in_business', 'ntn_number', 'email'
//            ];
//            foreach ($vendorFields as $field) {
//                $placeholder = '{{' . $field . '}}';
//                $value = $vendor->{$field} ?? '';
//                $letterContent = str_replace($placeholder, $value, $letterContent);
//            }
//        }
        if ($request->filled('employee_id')) {
            $employee = Employee::findOrFail($this->input['employee_id']);
            $employeeFields = [
                'name' => 'EmployeeName', 'employee_no' => 'EmployeeNo', 'date_of_birth' => 'DateOfBirth',
                'district_id' => 'District', 'employee_type' => 'EmployeeType', 'leave_date' => 'LeaveDate',
                'cnic' => 'Cnic', 'phone_no' => 'PhoneNo', 'cnic_issuance' => 'CnicIssuance',
                'cnic_expiry' => 'CnicExpiry', 'personal_email' => 'PersonalEmail', 'offical_email' => 'OfficalEmail',
                'department_id' => 'Department', 'report_to_id' => 'ReportTo', 'blood_group' => 'BloodGroup',
                'date_of_joining' => 'DateOfJoining', 'designation_id' => 'Designation',
                'residential_address' => 'ResidentialAddress', 'permanent_address' => 'PermanentAddress'
            ];
            foreach ($employeeFields as $field => $placeholder) {
                $value = $employee->{$field} ?? '';
                $letterContent = str_replace('{{' . $placeholder . '}}', $value, $letterContent);
            }
        }
        if ($request->filled('candidate_id')) {
            $candidate = ApplyJob::findOrFail($this->input['candidate_id']);
            $candidateFields = [
                'id' => 'CandidateID',
                'job_id' => 'JobID',
                'candidate_name' => 'CandidateName',
                'candidate_cnic' => 'CandidateCnic',
                'candidate_email' => 'CandidateEmail',
                'candidate_phone' => 'CandidatePhone',
                'candidate_gender' => 'CandidateGender',
                'linkedin_url' => 'LinkedinURL',
                'current_location' => 'CurrentLocation',
                'currently_employed' => 'CurrentlyEmployed',
                'currently_salary' => 'CurrentSalary',
                'expected_salary' => 'ExpectedSalary',
                'current_company' => 'CurrentCompany',
                'candidate_resume' => 'CandidateResume',
                'status' => 'Status',
                'deleted_at' => 'DeletedAt',
                'created_at' => 'CreatedAt',
                'updated_at' => 'UpdatedAt',
                'negotiated_salary' => 'NegotiatedSalary',
                'expected_joining_date' => 'ExpectedJoiningDate',
                'relocation' => 'Relocation',
                'relationship_las' => 'RelationshipLAS',
                'investigation' => 'Investigation',
                'candidate_travel' => 'CandidateTravel',
                'specially_able' => 'SpeciallyAble',
                'specialAbility' => 'SpecialAbility',
                'relation_with_las_employee' => 'RelationWithLASEmployee',
                'safety_guard_issue' => 'SafetyGuardIssue',
                'pool_bucket_type' => 'PoolBucketType',
                'applicant_type' => 'ApplicantType',
                'applier_id' => 'ApplierID',
                'technical_proposal' => 'TechnicalProposal',
                'financial_proposal' => 'FinancialProposal',
                'POCName' => 'POCName',
                'promisor_CNIC' => 'PromisorCNIC',
                'NTN' => 'NTN',
            ];
            foreach ($candidateFields as $field => $placeholder) {
                $value = $candidate->{$field} ?? '';
                $letterContent = str_replace('{{' . $placeholder . '}}', $value, $letterContent);
            }
        }

        $data['template_data'] = $letterContent;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendEmailTemplateForApproval(EmailTemplate $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',71)->first();
        $sp_approval_process=ApprovalProcess::query()->where('approval_process_id',71)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',71)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            $update=array('approval_status'=>2);
            EmailTemplate::query()->where('id',$item->id)->update($update);
            return resp(1,'Email Template request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Email Template request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
