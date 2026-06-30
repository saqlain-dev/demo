<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\BranchOffice;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Configuration\GeneratedLetter;
use App\Models\Designation;
use App\Models\District;
use App\Models\Employee;
use App\Models\HeadOffice;
use App\Models\TypeValue;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GeneratedLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('generated_letter_view');

        $data['items'] = GeneratedLetter::with(['TemplateId','EmployeeId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getTemplateContent(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
        ]);
        $vendor_id = $request->vendor_id;
        $employee_id = $request->employee_id;
        $template_id = $this->input['template_id'];
        $template = GeneralTemplates::query()->find($template_id);
        $letterContent = $template->template_data;

        if ($vendor_id){
            $vendor = Vendor::query()->findOrFail($vendor_id);
            $company_name = $vendor->company_name;
            $contact_person_1 = $vendor->contact_person_1;
            $address_1 = $vendor->address_1;
            $email_address = $vendor->email_address;
            $telephone_1 = $vendor->telephone_1;
            $fax_no = $vendor->fax_no;
            $main_area_of_business = $vendor->main_area_of_business;
            $other_area_of_business = $vendor->other_area_of_business;
            $year_in_business = $vendor->year_in_business;
            $ntn_number = $vendor->ntn_number;
            $email = $vendor->email;

            //Replace all Vendor Variables with actual values.

            $letterContent = str_replace('{{company_name}}', $company_name, $letterContent);
            $letterContent = str_replace('{{contact_person_1}}', $contact_person_1, $letterContent);
            $letterContent = str_replace('{{address_1}}', $address_1, $letterContent);
            $letterContent = str_replace('{{email_address}}', $email_address, $letterContent);
            $letterContent = str_replace('{{telephone_1}}', $telephone_1, $letterContent);
            $letterContent = str_replace('{{fax_no}}', $fax_no, $letterContent);
            $letterContent = str_replace('{{main_area_of_business}}', $main_area_of_business, $letterContent);
            $letterContent = str_replace('{{other_area_of_business}}', $other_area_of_business, $letterContent);
            $letterContent = str_replace('{{year_in_business}}', $year_in_business, $letterContent);
            $letterContent = str_replace('{{ntn_number}}', $ntn_number, $letterContent);
            $letterContent = str_replace('{{email}}', $email, $letterContent);
        }
        if ($employee_id){
            $employee = Employee::query()->findOrFail($employee_id);
            $employeeName = $employee->name;
            $employeeNo = $employee->employee_no;
            $dateOfBirth = $employee->date_of_birth;
            $district = District::query()->select('name')->find($employee->district_id)->name ?? 'N/A';
            $employeeType = TypeValue::query()->select('name')->find($employee->employee_type)->name ?? 'N/A';
            $leaveDate = $employee->leave_date;
            $cnic = $employee->cnic;
            $phoneNo = $employee->phone_no;
            $cnicIssuance = $employee->cnic_issuance;
            $cnicExpiry = $employee->cnic_expiry;
            $personalEmail = $employee->personal_email;
            $officalEmail = $employee->offical_email;
            $department = TypeValue::query()->select('name')->find($employee->department_id)->name ?? 'N/A';
            $reportTo = Employee::query()->select('name')->find($employee->report_to_id)->name ?? 'N/A';
            $bloodGroup = TypeValue::query()->select('name')->find($employee->blood_group)->name ?? 'N/A';
            $dateOfJoining = $employee->date_of_joining;
            $designation = Designation::query()->select('name')->find($employee->designation_id)->name ?? 'N/A';
            $residentialAddress = $employee->residential_address;
            $permanentAddress = $employee->permanent_address;
            // Replace variables with actual values
            $letterContent = str_replace('{{EmployeeName}}', $employeeName, $letterContent);
            $letterContent = str_replace('{{EmployeeNo}}', $employeeNo, $letterContent);
            $letterContent = str_replace('{{DateOfBirth}}', $dateOfBirth, $letterContent);
            $letterContent = str_replace('{{District}}', $district, $letterContent);
            $letterContent = str_replace('{{EmployeeType}}', $employeeType, $letterContent);
            $letterContent = str_replace('{{LeaveDate}}', $leaveDate, $letterContent);
            $letterContent = str_replace('{{Cnic}}', $cnic, $letterContent);
            $letterContent = str_replace('{{PhoneNo}}', $phoneNo, $letterContent);
            $letterContent = str_replace('{{CnicIssuance}}', $cnicIssuance, $letterContent);
            $letterContent = str_replace('{{CnicExpiry}}', $cnicExpiry, $letterContent);
            $letterContent = str_replace('{{PersonalEmail}}', $personalEmail, $letterContent);
            $letterContent = str_replace('{{OfficalEmail}}', $officalEmail, $letterContent);
            $letterContent = str_replace('{{Department}}', $department, $letterContent);
            $letterContent = str_replace('{{ReportTo}}', $reportTo, $letterContent);
            $letterContent = str_replace('{{BloodGroup}}', $bloodGroup, $letterContent);
            $letterContent = str_replace('{{DateOfJoining}}', $dateOfJoining, $letterContent);
            $letterContent = str_replace('{{Designation}}', $designation, $letterContent);
            $letterContent = str_replace('{{ResidentialAddress}}', $residentialAddress, $letterContent);
            $letterContent = str_replace('{{PermanentAddress}}', $permanentAddress, $letterContent);
        }
        $data['template_data'] = $letterContent;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function getTemplateContent1(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
        ]);
        $template_id = $this->input['template_id'];
        $template = GeneralTemplates::findOrFail($template_id);
        $letterContent = $template->template_data;
        if ($request->filled('vendor_id')) {
            $vendor = Vendor::findOrFail($this->input['vendor_id']);
            $vendorFields = [
                'company_name', 'contact_person_1', 'address_1', 'email_address',
                'telephone_1', 'fax_no', 'main_area_of_business', 'other_area_of_business',
                'year_in_business', 'ntn_number', 'email'
            ];
            foreach ($vendorFields as $field) {
                $placeholder = '{{' . $field . '}}';
                $value = $vendor->{$field} ?? '';
                $letterContent = str_replace($placeholder, $value, $letterContent);
            }
        }
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
        $data['template_data'] = $letterContent;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('generated_letter_create');

        $request->validate([
            'template_id' => 'required',
            'employee_id' => 'required',
            'letter_no' => 'required',
            'letter_content' => 'required',
            'letter_reference' => 'required',
            'letter_name' => 'required',
        ]);

        $employee_id = $this->input['employee_id'];
        $letterContent = $this->input['letter_content'];
        $employee = Employee::query()->findOrFail($employee_id);
        $employeeName = $employee->name;
        $employeeNo = $employee->employee_no;
        $dateOfBirth = $employee->date_of_birth;
        $district = District::query()->select('name')->find($employee->district_id)->name ?? 'N/A';
        $employeeType = TypeValue::query()->select('name')->find($employee->employee_type)->name ?? 'N/A';
        $leaveDate = $employee->leave_date;
        $cnic = $employee->cnic;
        $phoneNo = $employee->phone_no;
        $cnicIssuance = $employee->cnic_issuance;
        $cnicExpiry = $employee->cnic_expiry;
        $personalEmail = $employee->personal_email;
        $officalEmail = $employee->offical_email;
        $department = TypeValue::query()->select('name')->find($employee->department_id)->name ?? 'N/A';
        $reportTo = Employee::query()->select('name')->find($employee->report_to_id)->name ?? 'N/A';
        $bloodGroup = TypeValue::query()->select('name')->find($employee->blood_group)->name ?? 'N/A';
        $dateOfJoining = $employee->date_of_joining;
        $designation = Designation::query()->select('name')->find($employee->designation_id)->name ?? 'N/A';
        $residentialAddress = $employee->residential_address;
        $permanentAddress = $employee->permanent_address;

// Replace variables with actual values
        $letterContent = str_replace('{{EmployeeName}}', $employeeName, $letterContent);
        $letterContent = str_replace('{{EmployeeNo}}', $employeeNo, $letterContent);
        $letterContent = str_replace('{{DateOfBirth}}', $dateOfBirth, $letterContent);
        $letterContent = str_replace('{{District}}', $district, $letterContent);
        $letterContent = str_replace('{{EmployeeType}}', $employeeType, $letterContent);
        $letterContent = str_replace('{{LeaveDate}}', $leaveDate, $letterContent);
        $letterContent = str_replace('{{Cnic}}', $cnic, $letterContent);
        $letterContent = str_replace('{{PhoneNo}}', $phoneNo, $letterContent);
        $letterContent = str_replace('{{CnicIssuance}}', $cnicIssuance, $letterContent);
        $letterContent = str_replace('{{CnicExpiry}}', $cnicExpiry, $letterContent);
        $letterContent = str_replace('{{PersonalEmail}}', $personalEmail, $letterContent);
        $letterContent = str_replace('{{OfficalEmail}}', $officalEmail, $letterContent);
        $letterContent = str_replace('{{Department}}', $department, $letterContent);
        $letterContent = str_replace('{{ReportTo}}', $reportTo, $letterContent);
        $letterContent = str_replace('{{BloodGroup}}', $bloodGroup, $letterContent);
        $letterContent = str_replace('{{DateOfJoining}}', $dateOfJoining, $letterContent);
        $letterContent = str_replace('{{Designation}}', $designation, $letterContent);
        $letterContent = str_replace('{{ResidentialAddress}}', $residentialAddress, $letterContent);
        $letterContent = str_replace('{{PermanentAddress}}', $permanentAddress, $letterContent);
        $this->input['letter_content'] = $letterContent;
        try {
            DB::beginTransaction();
            $data['item'] = GeneratedLetter::query()->create($this->input);
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
    public function show(GeneratedLetter $generatedLetter): JsonResponse
    {
        $this->authorize('generated_letter_view');

        $data['item'] = $generatedLetter->load(['TemplateId','EmployeeId','created_by','updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GeneratedLetter $generatedLetter)
    {
        $this->authorize('generated_letter_update');

        $request->validate([
            'template_id' => 'required',
            'employee_id' => 'required',
            'letter_no' => 'required',
            'letter_content' => 'required',
            'letter_reference' => 'required',
            'letter_name' => 'required',
        ]);
        $employee_id = $this->input['employee_id'];
        $letterContent = $this->input['letter_content'];
        $employee = Employee::query()->findOrFail($employee_id);
        $employeeName = $employee->name;
        $employeeNo = $employee->employee_no;
        $dateOfBirth = $employee->date_of_birth;
        $district = District::query()->select('name')->find($employee->district_id)->name ?? 'N/A';
        $employeeType = TypeValue::query()->select('name')->find($employee->employee_type)->name ?? 'N/A';
        $leaveDate = $employee->leave_date;
        $cnic = $employee->cnic;
        $phoneNo = $employee->phone_no;
        $cnicIssuance = $employee->cnic_issuance;
        $cnicExpiry = $employee->cnic_expiry;
        $personalEmail = $employee->personal_email;
        $officalEmail = $employee->offical_email;
        $department = TypeValue::query()->select('name')->find($employee->department_id)->name ?? 'N/A';
        $reportTo = Employee::query()->select('name')->find($employee->report_to_id)->name ?? 'N/A';
        $bloodGroup = TypeValue::query()->select('name')->find($employee->blood_group)->name ?? 'N/A';
        $dateOfJoining = $employee->date_of_joining;
        $designation = Designation::query()->select('name')->find($employee->designation_id)->name ?? 'N/A';
        $residentialAddress = $employee->residential_address;
        $permanentAddress = $employee->permanent_address;

// Replace variables with actual values
        $letterContent = str_replace('{{EmployeeName}}', $employeeName, $letterContent);
        $letterContent = str_replace('{{EmployeeNo}}', $employeeNo, $letterContent);
        $letterContent = str_replace('{{DateOfBirth}}', $dateOfBirth, $letterContent);
        $letterContent = str_replace('{{District}}', $district, $letterContent);
        $letterContent = str_replace('{{EmployeeType}}', $employeeType, $letterContent);
        $letterContent = str_replace('{{LeaveDate}}', $leaveDate, $letterContent);
        $letterContent = str_replace('{{Cnic}}', $cnic, $letterContent);
        $letterContent = str_replace('{{PhoneNo}}', $phoneNo, $letterContent);
        $letterContent = str_replace('{{CnicIssuance}}', $cnicIssuance, $letterContent);
        $letterContent = str_replace('{{CnicExpiry}}', $cnicExpiry, $letterContent);
        $letterContent = str_replace('{{PersonalEmail}}', $personalEmail, $letterContent);
        $letterContent = str_replace('{{OfficalEmail}}', $officalEmail, $letterContent);
        $letterContent = str_replace('{{Department}}', $department, $letterContent);
        $letterContent = str_replace('{{ReportTo}}', $reportTo, $letterContent);
        $letterContent = str_replace('{{BloodGroup}}', $bloodGroup, $letterContent);
        $letterContent = str_replace('{{DateOfJoining}}', $dateOfJoining, $letterContent);
        $letterContent = str_replace('{{Designation}}', $designation, $letterContent);
        $letterContent = str_replace('{{ResidentialAddress}}', $residentialAddress, $letterContent);
        $letterContent = str_replace('{{PermanentAddress}}', $permanentAddress, $letterContent);
        $this->input['letter_content'] = $letterContent;
        try {
            DB::beginTransaction();
            $data['item'] = $generatedLetter->update($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneratedLetter $generatedLetter): JsonResponse
    {
        $this->authorize('generated_letter_delete');

        $data['item'] = $generatedLetter->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }

    public function saveSystemGeneratedLetter(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required',
            'letter_name' => 'required',
            'letter_no' => 'nullable',
            'is_system_generated' => 'required',
            'attachment' => 'nullable|file',
        ]);

        try {
            DB::beginTransaction();
            $data['item'] = GeneratedLetter::query()->create($this->input);

            if ($request->hasFile('attachment') && $request->file('attachment')->isValid()){
                $extension = $request->file('attachment')->getClientOriginalExtension();
                $attachmentPath = $request->file('attachment')->storeAs('images/generated_letters', time() . '_attachment.' . $extension, 'public');
                $data['item']->update(['attachment' => $attachmentPath]);
            }
            
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
