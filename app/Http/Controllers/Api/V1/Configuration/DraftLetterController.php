<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\DraftLetter;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Configuration\GeneratedLetter;
use App\Models\Designation;
use App\Models\District;
use App\Models\Employee;
use App\Models\TypeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DraftLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['items'] = DraftLetter::with(['TemplateId','EmployeeId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'template_id' => 'required',
            'employee_id' => 'required',
            'draft' => 'required',
            'letter_name' => 'required',
        ]);
        $employee_id = $this->input['employee_id'];
        $letterContent = $this->input['draft'];
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
        $this->input['draft'] = $letterContent;
        try {
            DB::beginTransaction();
            $data['item'] = DraftLetter::query()->create($this->input);
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
    public function show(DraftLetter $draftLetter): JsonResponse
    {
        $data['item'] = $draftLetter->load(['TemplateId','EmployeeId','created_by','updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DraftLetter $draftLetter)
    {
        $request->validate([
            'template_id' => 'required',
            'employee_id' => 'required',
            'draft' => 'required',
            'letter_name' => 'required',
        ]);
        $employee_id = $this->input['employee_id'];
        $letterContent = $this->input['draft'];
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
        $this->input['draft'] = $letterContent;
        try {
            DB::beginTransaction();
            $data['item'] = $draftLetter->update($this->input);
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
    public function destroy(DraftLetter $draftLetter): JsonResponse
    {
        $data['item'] = $draftLetter->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }
}
