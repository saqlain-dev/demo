<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Models\Admin\FinancialYear;
use App\Models\Admin\ItemVariant;
use App\Models\Admin\Library\BookIssued;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\EmployeeChangeLog;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\HR\Leaves\LeaveBalance;
use App\Models\HR\Leaves\LeaveBalanceDetail;
use App\Models\Reimbursement;
use App\Models\Country;
use Carbon\Carbon;
use DateTime;
use App\Models\Type;
use App\Models\User;
use App\Models\Shift;
use App\Models\District;
use App\Models\Employee;
use App\Models\Experience;
use App\Models\HeadOffice;
use App\Models\Designation;
use App\Models\BranchOffice;
use Illuminate\Http\Request;
use App\Models\Qualification;

use Illuminate\Http\Response;

use App\Models\ApprovalProcess;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\ExitEmployeeDetail;
use Illuminate\Support\Facades\DB;
use App\Models\ApprovalProcessList;
use App\Http\Controllers\Controller;
use App\Models\HR\Payscale\Payscale;
use Illuminate\Support\Facades\Storage;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Configuration\EmployeeStatusChange;
use App\Models\Configuration\PositionWiseAllowDeduct;
use App\Models\HR\Payroll\EmployeeAllowanceDeduction;
use Illuminate\Validation\ValidationException;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_view',
            'manage_consultant_view',
            'finance_board_members_view',
            'manage_payslip',
            'manage_single_payroll',
            'manage_audit_payroll',
            'manage_audit_employee_management',
            'manage_audit_consultant_management',
            'board_members'
        ]);

        //$employeeList= Employee::with('typeValues');
        $employeeList= Employee::with(['district','shift','headOffice','branchOffice','designation','marital','salutation','PayscaleLevel','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','religiousSect','qualification.certification','experience','reportTo','grade'])->get();
        foreach ($employeeList as $employee) {
            // Use the custom accessor to get project details
            $employee->ProjectDetail = $employee->project_details;
        }

        //$data['OrientationPlan']=$employeeList;
        return resp(1,'Successful!', $employeeList,Response::HTTP_CREATED);
    }
    public function addEmployee(){

        $data=array();
        $data['head_offices']=HeadOffice::with('branches')->where('status',1)->get();
        //$statement = DB::select("SELECT IDENT_CURRENT('employees') as nextID");
        $lastEmployee = DB::table('employees')
            ->select('employee_no')
            ->where('employee_no', 'LIKE', 'LAS-%')
            ->orderBy('employee_no', 'desc')
            ->first();

        $lastConstEmployee = DB::table('employees')
            ->select('employee_no')
            ->where('employee_no', 'LIKE', 'CON-%')
            ->orWhere('employee_type', '493')
            ->orderBy('employee_no', 'desc')
            ->first();
        if ($lastEmployee) {
            // Extract the numeric part from 'LAS-0001'
            $lastNumber = intval(str_replace('LAS-', '', $lastEmployee->employee_no));

            // Increment the number by 1
            $newNumber = $lastNumber + 1;

            // Format the new employee number (e.g., LAS-0002)
            $newEmployeeNo = 'LAS-' . sprintf('%04d', $newNumber);
        } else {
            // If there are no employees yet, start with 'LAS-0001'
            $newEmployeeNo = 'LAS-0001';
        }

        if ($lastConstEmployee) {
            // Extract the numeric part from 'LAS-0001'
            $lastConNumber = intval(str_replace('LAS-', '', $lastConstEmployee->employee_no));

            // Increment the number by 1
            $newConNumber = $lastConNumber + 1;

            // Format the new employee number (e.g., LAS-0002)
            $newConsultantNo = 'CON-' . sprintf('%04d', $newConNumber);
        } else {
            // If there are no employees yet, start with 'LAS-0001'
            $newConsultantNo = 'CON-0001';
        }
        $data['consultant_no'] = $newConsultantNo;
        $data['employee_no'] = $newEmployeeNo;
        //$data['employee_no']=sprintf('%04d', $statement[0]->nextID).date('dmy');
        $data['employee_document_type']=Type::getTypeValues('employee-document-type');
        $data['Shift']=Shift::all();
        $data['marital_status']=Type::getTypeValues('marital-status');
        $data['salutation']=Type::getTypeValues('salutation');
        $data['employee_type']=Type::getTypeValues('employee-type');
        $data['blood_group']=Type::getTypeValues('blood-group');
        $data['religion']=Type::getTypeValues('employee-religion');
        $data['parentage']=Type::getTypeValues('employee-parentage');
        $data['gender']=Type::getTypeValues('employee-gender');
        $data['reference_type']=Type::getTypeValues('employee-reference-type');
        $data['departments']=Type::getTypeValues('department-names');
        $data['religious_sect']=Type::getTypeValues('religious-sect');
        $data['grades']=Type::getTypeValues('grading');
        $data['designation']=$designations=Designation::all();
        foreach ($designations as $key => $designation) {
            $designations[$key]->payScales = Payscale::whereRaw("CHARINDEX(',' + ? + ',', ',' + position + ',') > 0", [(string)$designation->id])->with('Grading','Level')->get();
        }
        $data['designation']=$designations;
        $data['districts']=District::all();
        $data['employeeList']= Employee::with('designation')->get();
        $data['payscale'] = Payscale::all();
        $data['projects']= ProjectProfile::query()->where(['approval_status'=>1])->get();
        $data['grades']=Type::getTypeValues('grading');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'employee_create',
            'manage_consultant_create',
            'finance_board_members_create',
            'board_members'
        ]);

        try {
            DB::beginTransaction();
        $request->validate([
            //'name' => 'required',
            //'head_office_id' => 'required',
            //'shift_id' => 'required',
            //'date_of_birth' => 'required',
            //'marital_id' => 'required',
           // 'district_id' => 'required',
            //'employee_type' => 'required',
           // 'cnic' => 'required|numeric|digits:13',
           // 'cnic_issuance' => 'required',
            /*'cnic_expiry' => 'required|date|after:cnic_issuance',*/
            //'department_id' => 'required',
            //'date_of_joining' => 'required',
            //'designation_id' => 'required',
            //'parentage_id' => 'required',
            //'parentage_name' => 'required',
            //'religion_id' => 'required',
            //'gender_id' => 'required',
            //'reference' => 'required',
            //'residential_address' => 'required',
            //'permanent_address' => 'required',
            //'grade' => 'required',
            //'payscale_level' => 'required',
            //'phone_no' => 'required|numeric|digits_between:1,13',
            'offical_email' => 'email|unique:employees,offical_email',
            //'employee_no' => 'required',
            //'project_id.*' => 'required',
            'employee_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'card_status' => 'nullable|in:0,1',
        ]/*, [
            'cnic_expiry.after' => 'The CNIC expiry date must be greater than the CNIC issuance date.',
        ]*/);
        $employeeExists = Employee::query()->where('employee_no', $this->input['employee_no'])->first();
        if ($employeeExists) {
            return resp('0', 'Record Cannot be Added! Employee already Exist', false, Response::HTTP_OK);
        }
        if($request->hasFile('emp_profile')) {

            $responce = $this->saveEmployeeProfile($request, 'empProfile');

            if ($responce) {
                $this->input['emp_profile'] = $responce;
            }
        }else{
            unset($this->input['emp_profile']);
        }

        if($request->project_id){

            $this->input['project_id']=$request->project_id;

        }else{
            unset($this->input['project_id']);
        }
        $this->input['date_of_birth']=date('Y-m-d',strtotime($request->date_of_birth));
        $this->input['cnic_issuance']=date('Y-m-d',strtotime($request->cnic_issuance));
        if($request->cnic_expiry != ""){
            $this->input['cnic_expiry']=date('Y-m-d',strtotime($request->cnic_expiry));
        }
        $this->input['date_of_joining']=date('Y-m-d',strtotime($request->date_of_joining));
        $this->input['Attendance_Id']=$request->attendance_id;
        unset($this->input['attendance_id']);

        if($request->file('employee_card')){
            $employeeCard=$this->saveEmployeCard($request,'employee_card');
            $this->input['employee_card']=$employeeCard;
            $this->input['card_status']=$request->card_status??0;
        }
        $employee=Employee::query()->create( $this->input);
        if($employee){
            $position_allowance_deduction=PositionWiseAllowDeduct::query()->where('position_id',$employee->designation_id)->get();
            foreach($position_allowance_deduction as $allowance_deduction_item){

                $allowance_deduction=AllowanceDeduction::query()->findOrFail($allowance_deduction_item['allowance_deduction_id']);
                $emp_allowance=EmployeeAllowanceDeduction::query()->where('employee_id',$employee->id)->where('allowance_deduction_id',$allowance_deduction_item['allowance_deduction_id'])->first();
                if(empty($emp_allowance)){
                    $insert=array(
                        "employee_id"=>$employee->id,
                        "allowance_deduction_id"=>$allowance_deduction_item['allowance_deduction_id'],
                        "description"=>$allowance_deduction->description,
                        "category"=>$allowance_deduction->category,
                        "calculated_by"=>$allowance_deduction->calculated_by,
                        "value"=>$allowance_deduction->value,
                        "isGlobal"=>0,
                        "isTaxable"=>$allowance_deduction->is_taxable,
                        "IsVariableAllowance"=>$allowance_deduction->IsVariableAllowance,
                    );
                    EmployeeAllowanceDeduction::query()->create($insert);
                }else{
                    $emp_allowance->description=$allowance_deduction->description;
                    $emp_allowance->category=$allowance_deduction->category;
                    $emp_allowance->calculated_by=$allowance_deduction->calculated_by;
                    $emp_allowance->value=$allowance_deduction->value;
                    $emp_allowance->isTaxable=$allowance_deduction->is_taxable;
                    $emp_allowance->IsVariableAllowance=$allowance_deduction->IsVariableAllowance;
                    $emp_allowance->save();
                }
            }
        }
            DB::commit();
            return resp('1', 'Successful!', $employee, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
    public function updateCard(Request $request, $id)
    {
      
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'employee_card' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'card_status' => 'nullable|in:0,1',
        ]);

        if($request->file('employee_card')){
            $employeeCard=$this->saveEmployeCard($request,'employee_card');
            $employee->update([
                'card_status'=>$request->card_status,
                'employee_card'=>$employeeCard,
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee,
        ], 200);
    }

    public function saveEmployeCard($request,$folder){

        
        $file = $request->file('employee_card');
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

    public function chnagePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'password' => ['required'],
        ]);
        try {

            $user = User::query()->where('id', auth()->user()->id)->where('user_type',1)->first();
            if (! $user || ! Hash::check($request->old_password, $user->password)) {
                throw ValidationException::withMessages([
                    'old_password' => ['The old password are incorrect.'],
                ]);
            }else{
                DB::beginTransaction();
                User::query()->where('id',$user->id)->update(array('password'=>bcrypt($request->password)));
                DB::commit();
                return resp('1', 'Password updated Successfully!', [], Response::HTTP_CREATED);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function saveEmployeeProfile($request,$folder){

        $file = $request->file('emp_profile');
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

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'employee_view',
            'manage_consultant_view',
            'finance_board_members_view',
            'manage_employee_portal',
            'manage_audit_payroll',
            'manage_audit_employee_management',
            'manage_audit_consultant_management',
            'board_members'
        ]);

        $employee= Employee::with(['district','userProfile','shift','headOffice','branchOffice','designation','marital','salutation','PayscaleLevel','religiousSect','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification.certification','experience','reportTo','employeeChnageStatus','grade','employeeDocuments.documentType','empContract.employeeContractDetail.contractType','empContracts.employeeContractDetail.contractType', 'employeeTimesheet.employeeSheetDetail','generatedLetter','complainAgainsts'=>['complainFrom','department','complainAgainst'],'complainFromEmployees' =>['complainFrom','department','complainAgainst']])->findOrFail($id);
        $employee->ProjectDetail = $employee->project_details;
        return resp(1,'Successful!', $employee,Response::HTTP_CREATED);
    }

    public function downloadEmployeeFiles($id)
    {
        try {
            $employee = Employee::with('employeeDocuments.documentType')->findOrFail($id);

            $employeeNameSlug = Str::slug($employee->name, '_'); // e.g., nirdosh_kumar
            $zipFileName = "{$employeeNameSlug}_documents.zip";

            $zipFolder = public_path('zips');
            $zipFullPath = "{$zipFolder}/{$zipFileName}";

            // Make sure the zips folder exists
            if (!File::exists($zipFolder)) {
                File::makeDirectory($zipFolder, 0755, true);
            }

            $zip = new ZipArchive;

            if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return response()->json(['error' => 'Failed to create zip file'], 500);
            }

            // Add employee documents to zip organized by document type
            foreach ($employee->employeeDocuments as $doc) {
                $typeSlug = Str::slug($doc->documentType->name ?? 'Unknown', '_');
                $publicRelativePath = $doc->document_path; // e.g., uploads/media/employee/employee_documents/filename.pdf
                $absoluteFilePath = public_path($publicRelativePath);

                if (file_exists($absoluteFilePath)) {
                    $fileName = basename($absoluteFilePath);
                    $zip->addFile($absoluteFilePath, "{$employeeNameSlug}/{$typeSlug}/{$fileName}");
                }
            }

            $zip->close();

            // Confirm ZIP was created
            if (!file_exists($zipFullPath)) {
                return response()->json(['error' => 'ZIP file not found after creation'], 500);
            }

            return response()->json([
                'zip_url' => "/zips/{$zipFileName}" // relative path for frontend
            ]);

        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $this->authorizeAny([
            'employee_update',
            'manage_consultant_update',
            'finance_board_members_update',
            'manage_employee_portal',
            'board_members'
        ]);

        try {
            DB::beginTransaction();
        $request->validate([
            //'name' => 'required',
            //'head_office_id' => 'required',
            //'shift_id' => 'required',
           //'date_of_birth' => 'required',
            //'marital_id' => 'required',
            //'district_id' => 'required',
            //'employee_type' => 'required',
            //'cnic' => 'required|numeric|digits:13',
            //'cnic_issuance' => 'required',
           // 'cnic_expiry' => 'required|date|after:cnic_issuance',
            //'department_id' => 'required',
            //'date_of_joining' => 'required',
           // 'designation_id' => 'required',
           // 'parentage_id' => 'required',
            //'parentage_name' => 'required',
            //'religion_id' => 'required',
            //'gender_id' => 'required',
            //'reference' => 'required',
            //'residential_address' => 'required',
            //'grade' => 'required',
            //'payscale_level' => 'required',
           // 'permanent_address' => 'required',
            //'phone_no' => 'required|numeric|digits_between:1,13',
            'offical_email' => 'email|unique:employees,offical_email,' .$employee->id. ',id',
            //'employee_no' => 'required|unique:employees,employee_no,' .$employee->id. ',id',
            //'project_id' => 'required|array|min:1',
            //'project_id.*' => 'required',
            //'payscale' => 'required',
        ]/*, [
            'cnic_expiry.after' => 'The CNIC expiry date must be greater than the CNIC issuance date.',
        ]*/);

        if($request->hasFile('emp_profile')){

            $responce=$this->saveEmployeeProfile($request,'empProfile');
            $this->input['emp_profile']=$responce;
        }else{
            unset($this->input['emp_profile']);
        }

        if($request->project_id){

            $this->input['project_id']=json_encode($request->project_id);

        }else{
            unset( $this->input['project_id']);
        }


        $this->input['date_of_birth']=date('Y-m-d',strtotime($request->date_of_birth));
        $this->input['cnic_issuance']=date('Y-m-d',strtotime($request->cnic_issuance));
        if($request->cnic_expiry != ""){
            $this->input['cnic_expiry']=date('Y-m-d',strtotime($request->cnic_expiry));
        }

        $this->input['date_of_joining']=date('Y-m-d',strtotime($request->date_of_joining));
        $this->input['Attendance_Id']=$request->attendance_id;
        unset($this->input['attendance_id']);
        $updateEmployee=Employee::query()->where('id', $employee->id)->update( $this->input);
        $employeeDetail=Employee::with(['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','religiousSect','reportTo'])->findOrFail($employee->id);
        if($request->employee_type == 14 || $request->employee_type == 16 || $request->employee_type == 17 || $request->employee_type == 18){
            User::query()->where('employee_id',$employeeDetail->id)->update(array('status'=>0));
        }else{
            User::query()->where('employee_id',$employeeDetail->id)->update(array('designation_id'=>$request->designation_id));
        }
        if($updateEmployee){

            if($request->employee_type == 13){
                updateEmployeeYearlyLeave($employeeDetail->id);
            }
            $position_allowance_deduction=PositionWiseAllowDeduct::query()->where('position_id',$employee->designation_id)->get();
            foreach($position_allowance_deduction as $allowance_deduction_item){

                $allowance_deduction=AllowanceDeduction::query()->findOrFail($allowance_deduction_item['allowance_deduction_id']);
                $emp_allowance=EmployeeAllowanceDeduction::query()->where('employee_id',$employeeDetail->id)->where('allowance_deduction_id',$allowance_deduction_item['allowance_deduction_id'])->first();
                if(empty($emp_allowance)){
                    $insert=array(
                        "employee_id"=>$employeeDetail->id,
                        "allowance_deduction_id"=>$allowance_deduction_item['allowance_deduction_id'],
                        "description"=>$allowance_deduction->description,
                        "category"=>$allowance_deduction->category,
                        "calculated_by"=>$allowance_deduction->calculated_by,
                        "value"=>$allowance_deduction->value,
                        "isGlobal"=>0,
                    );
                    EmployeeAllowanceDeduction::query()->create($insert);
                }else{
                    $emp_allowance->description=$allowance_deduction->description;
                    $emp_allowance->category=$allowance_deduction->category;
                    $emp_allowance->calculated_by=$allowance_deduction->calculated_by;
                    $emp_allowance->value=$allowance_deduction->value;
                    $emp_allowance->save();
                }
            }
        }
            DB::commit();
            return resp('1', 'Successful!', $employeeDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
    }

    public function updateEmployeeLeave(Request $request)
    {
        $data=array();
        $employeeId=$request->employee_id;
        $financialYear= FinancialYear::query()->where('status', 1)->first();
        $leave_balance_details= LeaveBalanceDetail::query()->where('FYID', $financialYear->id)->get();
        $end_date=date('Y-m-d');
        $months = DB::select("select DATEDIFF(MONTH, '".$financialYear->start_date."', '".$end_date."') as month");

        if($leave_balance_details){
            foreach($leave_balance_details as $leave_balance){

                $checkLeave= LeaveBalance::query()->where('FYID', $leave_balance['FYID'])->where('LeaveTypeID',$leave_balance['LeaveTypeID'])->where('EmployeeID',$employeeId)->first();
                if(empty($checkLeave)){
                    $month=@$months[0]->month;
                    $monthlyBalnce=($leave_balance['LeaveBalance'] / 12) * (12 - intval($month));

                    $insert=array(
                        'EmployeeID'=>$employeeId,
                        'LeaveTypeID'=>$leave_balance['LeaveTypeID'],
                        'Balance'=>round($monthlyBalnce),
                        'FYID'=>$leave_balance['FYID'],
                    );
                    LeaveBalance::query()->insert($insert);
                }
            }
        }



        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }

    // Qualification
    public function qualification($id){
        $this->authorizeAny([
            'manage_employee_portal',
            'dashboard-governance'
        ]);

        $data['qualificationList']= Qualification::query()->where('employee_id',$id)->get();
        $data['certification']= Type::getTypeValues('degree-certification');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function saveQualification(Request $request){
        $this->authorizeAny([
            'manage_employee_portal',
            'dashboard-governance'
        ]);

        $request->validate([
            'certification' => 'required',
            'speciality' => 'required',
            'institute' => 'required',
            'year' => 'required',
            'grade' => 'required'
        ]);
        $qualification=Qualification::query()->create( $this->input);

        return resp(1,'Successful!', $qualification,Response::HTTP_CREATED);
    }
    public function updateQualification(Request $request){

            $qualification=Qualification::query()->findOrFail($request->id);
            $request->validate([
                'certification' => 'required',
                'speciality' => 'required',
                'institute' => 'required',
                'year' => 'required',
                'grade' => 'required'
            ]);
            $qualification->certification=$request->certification;
            $qualification->speciality=$request->speciality;
            $qualification->institute=$request->institute;
            $qualification->year=$request->year;
            $qualification->grade=$request->grade;
            $qualification->save();

            return resp(1,'Successful!', $qualification,Response::HTTP_CREATED);


    }
    public function deleteQualification(Qualification $item)
    {
        $item->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }

    // Experience
    public function experience($id){
        $data['experienceList']= Experience::query()->where('employee_id',$id)->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function saveExperience(Request $request){
        $this->authorizeAny([
            'manage_employee_portal',
            'dashboard-governance'
        ]);

        $request->validate([
            'organization' => 'required',
            'designation' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);
        $year=$this->calculateTimePeriod($request->from,$request->to);
        $this->input['years']=$year;
        $this->input['from']=date('Y-m-d',strtotime($request->from));
        $this->input['to']=date('Y-m-d',strtotime($request->to));
        $experience=Experience::query()->create( $this->input);

        return resp(1,'Successful!', $experience,Response::HTTP_CREATED);
    }
    function calculateTimePeriod($from,$to){
        $date1 = new DateTime(date('Y-m-d',strtotime($from)));
        $date2 = new DateTime(date('Y-m-d',strtotime($to)));
        $diff = $date1->diff($date2);

        return  $diff->y . " years, " . $diff->m." months, ".$diff->d." days ";
    }
    public function updateExperience(Request $request, Experience $item){

        $request->validate([
            'organization' => 'required',
            'designation' => 'required',
            'from' => 'required',
            'to' => 'required',
        ]);
        $year=$this->calculateTimePeriod($request->from,$request->to);
        $item->organization=$request->organization;
        $item->designation=$request->designation;
        $item->from=date('Y-m-d',strtotime($request->from));
        $item->to=date('Y-m-d',strtotime($request->to));
        $item->years=$year;
        $item->save();

        return resp(1,'Successful!', $item,Response::HTTP_CREATED);


    }
    public function deleteExperience(Experience $item)
    {
        $item->delete();
        return resp(1,'Successful!', [],Response::HTTP_CREATED);
    }

    public function updatePicture(Request $request, Employee $item)
    {

        $request->validate([
            'emp_profile' => 'required|file',
        ]);
        if($request->file('emp_profile')){
            $responce=$this->saveEmployeeProfile($request,'empProfile');
            $this->input['emp_profile']=$responce;
        }

        Employee::query()->where('id', $item->id)->update( $this->input);
        $employee= Employee::with(['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'])->findOrFail($item->id);
        return resp(1,'Successful!', $employee,Response::HTTP_CREATED);

    }
    public function getLeaveBalanceByID()
    {
        $this->authorizeAny([
            'manage_leave_balance',
            'manage_employee_portal',
        ]);

        $employeeNumber= $this->input['employeeNumber'];
        $departmentId= $this->input['departmentId'];
        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $leaveData = DB::select('EXEC get_leave_balances ?, ? , ?', [$employeeNumber, $departmentId,$FYID->id]);
        $groupedData = [];

        foreach ($leaveData as $leave) {
            $employeeId = $leave->id; // Assuming 'id' is the employee ID field
            $employeeName = $leave->employee_name;
            $employeeDepartment = $leave->Department;
            $employeeDesignation = $leave->Designation;
            $employeeHeadOffice = $leave->HeadOffice;
            $employeeBranchOffice = $leave->BranchOffice;
            $employeeNo = $leave->employee_no;
            $employeeDateOfJoining = $leave->date_of_joining;

            if (!isset($groupedData[$employeeId])) {
                // Initialize the employee entry if it doesn't exist
                $groupedData[$employeeId] = [
                    'employee_name' => $employeeName,
                    'employee_dep' => $employeeDepartment,
                    'employee_desg' => $employeeDesignation,
                    'employee_head_office' => $employeeHeadOffice,
                    'employee_branch_office' => $employeeBranchOffice,
                    'employee_no' => $employeeNo,
                    'employee_date_of_joining' => $employeeDateOfJoining,
                    'leave_balances' => [],
                ];
            }

            // Add leave balance data to the employee's entry
            $groupedData[$employeeId]['leave_balances'][] = [
                'leave_type_id' => $leave->leave_type_id,
                'leave_type' => $leave->leave_type,
                'leave_balance' => $leave->leave_balance,
                'availed_balance' => $leave->availed_balance,
                'entitlement_balance' => $leave->entitlement_balance,
                'pending_leave_requests' => $leave->pending_leave_requests,
            ];
        }

// Convert associative array to indexed array
        $groupedData = array_values($groupedData);
        $data['leave_balance']=$groupedData;
        $data['leave_types']=Type::getTypeValues('leave-type');
        return resp(1,'Successfully!', $data,Response::HTTP_OK);
    }

    public function employeeStatusChange(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'employee_id' => 'required',
                'status_change_type' => 'required',
                'comments' => 'required',
            ]);
            if($request->hasFile('attachment')) {

                $responce = $this->saveFile($request, 'EmployeeStatusChangeAttachment');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }

            $employeeStatusChange=EmployeeStatusChange::query()->create($this->input);
            DB::commit();
            return resp('1', 'Employee status change request added Successfully!', $employeeStatusChange->load('employeeDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function saveFile($request,$folder)
    {
        $file = $request->file('attachment');
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
    public function sendEmpStatusChangeReqForApproval(EmployeeStatusChange $item)
    {
        $approval_process_name=ApprovalProcessName::query()->where('id',33)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',33)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',33)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
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
            EmployeeStatusChange::query()->where('id',$item->id)->update($update);
            return resp(1,'Complaint request send for Approval.', $Approval,Response::HTTP_OK);
        }else{
            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Complaint request approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function employeeStatusChangeRequestListing()
    {
       $employeeStatusChangeListing=EmployeeStatusChange::query()->with('employeeDetail')->get();
        foreach ($employeeStatusChangeListing as $key => $statusListing) {
            $employeeStatusChangeListing[$key]['approval_request']=getNextApproval(33,auth()->user()->designation_id,$statusListing->id);
            $employeeStatusChangeListing[$key]['approval_request_status']=checkApprovalRequestStatus(33,$statusListing->id);


        }
        $data['employeeStatusChangeListing']=$employeeStatusChangeListing;
        return resp(0,'Successfully!', $data,Response::HTTP_OK);
    }
    public function employeeApprovedStatusChangeRequestListing()
    {
       $employeeStatusChangeListing=EmployeeStatusChange::query()->where('request_status',0)->where('approval_status',1)->with('employeeDetail')->get();
        foreach ($employeeStatusChangeListing as $key => $statusListing) {
            $employeeStatusChangeListing[$key]['approval_request']=getNextApproval(33,auth()->user()->designation_id,$statusListing->id);
            $employeeStatusChangeListing[$key]['approval_request_status']=checkApprovalRequestStatus(33,$statusListing->id);


        }
        $data['employeeStatusChangeListing']=$employeeStatusChangeListing;
        return resp(0,'Successfully!', $data,Response::HTTP_OK);
    }
    public function storeExitEmployeeDetails(Request $request)
    {
        $request->validate([
            'employee_type' => 'required',
            'remarks' => 'required',
            'leaving_reason' => 'required',
            'reason_details' => 'required',
            'employee_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data['item'] = ExitEmployeeDetail::query()->create($this->input);
            Employee::where('id',$data['item']->employee_id)->update(['exit_employee_detail_id' => $data['item']->id, 'employee_type' => $data['item']->employee_type]);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function employeeOffBoarding(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data['Reimbursements']=Reimbursement::query()->withSum('Expenses', 'amount')->where('employee_id',$request->employee_id)->where('is_voucher_posted',0)->get()->sum('expenses_sum_amount');
            $data['claim_travel_expenses']=ClaimTravelExpense::query()->withSum('ExpenseDetail', 'amount')->where('employee_id',$request->employee_id)->where('is_voucher_posted',0)->get()->sum('expense_detail_sum_amount');
            $data['court_expenses']=CourtExpense::query()->where('employee_id',$request->employee_id)->where('is_voucher_posted',0)->sum('amount');
            $data['advance_salary'] = AdvanceSalary::query()
                ->withSum(['installments' => function ($query) {
                       $query->whereNull('PayrollDetailId');
                }], 'due_amount')
                ->where('employee_id', $request->employee_id)
                ->get();
            $data['issue_items'] = ItemVariant::query()->with('item','inventory','location','assignToEmploy')->where('assign_to_emp',$request->employee_id)->get();
            DB::commit();
            return resp('1', 'Successfully!', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function employeeAssets(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $data['issue_items'] = ItemVariant::query()->with('item','inventory','location','assignToEmploy')->where('assign_to_emp',$request->employee_id)->get();
            $data['issue_books'] = BookIssued::query()->with(['BookId.book_type','book','EmployeeId' => ['district','shift','headOffice','branchOffice','designation','marital','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification','experience','reportTo'],'created_by','updated_by'])->where('employee_id', $request->employee_id)->get();
            DB::commit();
            return resp('1', 'Successfully!', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getEmployeeStatusReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|before_or_equal:end_date|date_format:Y-m-d',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
        ]);
        try {
            DB::beginTransaction();

            $data['employee_status_change_log']=EmployeeChangeLog::query()->with(['employeeDetail'=>['district','userProfile','shift','headOffice','branchOffice','designation','marital','salutation','PayscaleLevel','employeeTyp','department','bloodGroupName','parentage','religion','gender','referenceName','user','report','qualification.certification','experience','reportTo']])->whereBetween('effective_date', [$request->input('start_date'), $request->input('end_date')])->get();
            DB::commit();
            return resp('1', 'Successfully!', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function empListing(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date', //2025-09-01
            'end_date'   => 'required|date|after_or_equal:start_date', //2025-09-01
        ]);

        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        $employees = Employee::query()
            ->whereBetween('date_of_joining', [$startDate, $endDate])
            ->orWhereBetween('leave_date', [$startDate, $endDate])
            ->get();

        return resp(
            '1',
            'Employees fetched successfully!',
            $employees,
            Response::HTTP_OK
        );
    }
    
}
