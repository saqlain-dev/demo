<?php

namespace App\Http\Controllers\Api\V1\HR\Payroll;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeePayrollDetail;
use App\Models\EmployeePayrollMaster;
use App\Models\EmployeePayrollPreGrossSalary;
use App\Models\HR\AdvanceSalary\AdvanceSalaryInstallment;
use App\Models\HR\AdvanceSalary\LoanSettlement;
use App\Models\HR\Payroll\EmployeeAllowanceDeduction;
use App\Models\HR\Payroll\EmployeeLedger;
use App\Models\HR\Payroll\EmployeePayrollSegregation;
use App\Models\HR\Payroll\EmployeeSalarySegregation;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use App\Models\HR\Payroll\PayrollTaxRates;
use App\Models\HR\PreGrossSalaryAllowances;
use App\Models\Program\Project\ProjectProfile;
use App\Models\SalaryAllowanceDeduction;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeePayrollMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_payroll_listing',
            'manage_audit_payroll',
        ]);

        $data['payroll_listing']=EmployeePayrollMaster::query()->with('payrollDetail.allowanceDeduction','createdBy')->orderByDesc('id')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeePayrollMaster $payroll)
    {

        $this->authorizeAny([
            'manage_payroll_listing',
            'manage_audit_payroll',
        ]);

        $data['payroll']=$payroll->load('payrollDetail.allowanceDeduction','payrollDetail.employeeDetail','payrollDetail.employeeDetail.department','payrollDetail.employeeDetail.designation','payrollDetail.employeeDetail.headOffice','payrollDetail.employeeDetail.grade','payrollDetail.employeeDetail.branchOffice','payrollDetail.BankName','payrollDetail.preGrossSalaryAllowances','payrollDetail.salarySegregation');

        //$data['projects']=ProjectProfile::query()->where('approval_status',1)->get();
        $masterPayrollId=$payroll->id;
        $data['projects'] = ProjectProfile::query()
            ->where('approval_status', 1)
            ->whereIn('id', function ($query) use ($masterPayrollId) {
                $query->select('ProjectId')
                    ->from('employee_payroll_segregations')
                    ->join('employee_payroll_details', 'employee_payroll_segregations.PayrollDetailId', '=', 'employee_payroll_details.id')
                    ->join('employee_payroll_masters', 'employee_payroll_details.PayrollMasterId', '=', 'employee_payroll_masters.id')
                    ->whereNotNull('employee_payroll_segregations.ProjectId') // Ensure valid project IDs
                    ->where('employee_payroll_masters.id', $masterPayrollId); // Filter by master payroll ID
            })
            ->get();

        $data['allowance_deducation'] = AllowanceDeduction::query()->get();
        $data['approval_request']=getNextApproval(31,auth()->user()->designation_id,$payroll->id);
        $data['approval_request_status']=checkApprovalRequestStatus(31,$payroll->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeePayrollMaster $employeePayrollMaster)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeePayrollMaster $employeePayrollMaster)
    {
        //
    }

    public function generatePayroll(Request $request)
    {
        $this->authorizeAny([
            'manage_generate_payroll',
        ]);

        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date',
        ]);
        $from=date('Ymd',strtotime($this->input['from_date']));
        $to=date('Ymd',strtotime($this->input['to_date']));
        $department=$this->input['department'];
        $employeetype=$this->input['employee_type'];
        $branch_office=$this->input['branch_office'];
        $gender=$this->input['gender'];
        $generatePayroll = DB::select('EXEC [dbo].[usp_GetGeneratePayrollData] ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?', [
            0,      // Parameter 1
            1,      // Parameter 2
            $department,   // Parameter 3
            null,   // Parameter 4
            $gender,   // Parameter 5
            $branch_office,   // Parameter 6
            $from, // Parameter 7
            $to, // Parameter 8
            $employeetype,   // Parameter 9
            '0-0',  // Parameter 10
            0,      // Parameter 11
            0,      // Parameter 12
            null,   // Parameter 13
            null,   // Parameter 14
            $from, // Parameter 15
            $to, // Parameter 16
        ]);
        if($generatePayroll){
            foreach($generatePayroll as $key=> $payroll){

                $empsalaryPreGrossSalary=PreGrossSalaryAllowances::query()->with('allowanceType')->get();

                foreach($empsalaryPreGrossSalary as $pkey => $preGross){

                     $inserPreGrossSalrySegregation[$pkey]=array(
                         'allowance_percentage'=>$preGross['allowance_percentage'],
                         'allowance_name'=>$preGross->allowanceType->name,
                         'salary_amount'=>($preGross['allowance_percentage'] / 100) * $payroll->NetSalary,
                     );

                }
                $generatePayroll[$key]->PreGrossSalrySegregation=$inserPreGrossSalrySegregation;
            }
        }

        $data['payroll']=$generatePayroll;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function payrollDropDown()
    {
        $data['department_list']=Type::getTypeValues('department-names');
        $data['designation_list']=Designation::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function createPayrollBulk(Request $request)
    {
       // return $request->PayrollMasterDetail;
        DB::beginTransaction();
        try {


            $request->validate([
                'PayrollMaster.PaymentDate' => 'required|date',
                'PayrollMaster.TotalMonthlySalary' => 'required|numeric|min:0',
                'PayrollMaster.TotalNetSalary' => 'required|numeric|min:0',
                'PayrollMaster.TotalDeductions' => 'required|numeric|min:0',
                'PayrollMaster.TotalAllowances' => 'required|numeric|min:0',
                'PayrollMaster.TotalNetPay' => 'required|numeric|min:0',
            ]);


            $PayrollMaster=$request->PayrollMaster;
            $PayrollMasterDetail=$request->PayrollMasterDetail;
            $OtherAllowanceDeduction=$request->AllowanceDeduction;

            $PayrollMaster['PaymentDate']=date('Y-m-d',strtotime( $PayrollMaster['PaymentDate']));
            $PayrollMaster['PayPeriodFrom']=date('Y-m-d',strtotime( $PayrollMaster['PayPeriodFrom']));
            $PayrollMaster['PayPeriodTo']=date('Y-m-d',strtotime( $PayrollMaster['PayPeriodTo']));
            $PayrollMaster['DateCreated']=date('Y-m-d h:i:s');
            //$PayrollMaster['created_by']=auth()->user()->id;

            $MasterPayroll=EmployeePayrollMaster::query()->create($PayrollMaster);

            $employeeLedgerNumber=0;
            $lastEmployeeLedger = EmployeeLedger::query()->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();

            if ($lastEmployeeLedger === null) {
                $employeeLedgerNumber = $employeeLedgerNumber + 1;
            } else {
                $employeeLedgerNumber = $lastEmployeeLedger->EmployeeLedgerNumber + 1;
            }
            for ($i = 0; $i < count($PayrollMasterDetail); $i++)
            {

                $monthlySalary = $PayrollMasterDetail[$i]['MonthlySalary'] ?? 0.0;
                $netSalary = $PayrollMasterDetail[$i]['NetSalary'] ?? 0.0;
                $NetPay = $PayrollMasterDetail[$i]['NetPay'] ?? 0.0;
                $taxableAllowances = 0.0;
                $taxableDeductions = 0.0;
                $taxableOtherAllowance = 0.0;
                $taxableOtherDeduction = 0.0;
                $taxableSalary = 0.0;
                $paidSalary = 0.0;
                $futureTaxableSalary = 0.0;
                $currentTaxableSalary = 0.0;
                $medicalExemption = 0.0;

                $empId = $PayrollMasterDetail[$i]['EmployeeID'];

                $employeeAllowanceDeduction = EmployeeAllowanceDeduction::query()->where('employee_id',$empId)->get();
                $employeeSalarySegregation=EmployeeSalarySetup::query()->with('salarySegregation.projectDetail')->where('employee_id',$empId)->select('id','employee_id')->first();
                $PayrollMasterDetail[$i]['PaymentDate']=date('Y-m-d',strtotime($PayrollMasterDetail[$i]['PaymentDate']));
                $PayrollMasterDetail[$i]['PayPeriodFrom']=date('Y-m-d',strtotime($PayrollMasterDetail[$i]['PayPeriodFrom']));
                $PayrollMasterDetail[$i]['PayPeriodTo']=date('Y-m-d',strtotime($PayrollMasterDetail[$i]['PayPeriodTo']));
                $PayrollMasterDetail[$i]['DateCreated']=date('Y-m-d h:i:s');
                $PayrollMasterDetail[$i]['GeneratedVia']='Bulk';
                $PayrollMasterDetail[$i]['created_by']=auth()->user()->id;
                $PayrollMasterDetail[$i]['PayrollMasterId']=$MasterPayroll->id;
                $PayrollMasterDetail[$i]['EmployeeId']=$empId;
                $PayrollMasterDetail[$i]['arrears']=$PayrollMasterDetail[$i]['emp_arrears'];
                $PayrollMasterDetail[$i]['tax']=$PayrollMasterDetail[$i]['TAX'];
                $PayrollMasterDetail[$i]['EOBI']=$PayrollMasterDetail[$i]['EOBIDeductions'];
                $PayrollMasterDetail[$i]['unpaidInstallment']=$PayrollMasterDetail[$i]['UnpaidInstallment'];
                $PayrollMasterDetail[$i]['allowance']=$PayrollMasterDetail[$i]['Allowances'];
                $PayrollMasterDetail[$i]['deduction']=$PayrollMasterDetail[$i]['Deductions'];
                $PayrollMasterDetail[$i]['remarks']=$PayrollMasterDetail[$i]['emp_remarks'];
                $PayrollMasterDetail[$i]['AbsentWOLeave']=$PayrollMasterDetail[$i]['AbsentWOLeave'];
                $PayrollMasterDetail[$i]['AbsentDaysAmount']=$PayrollMasterDetail[$i]['AbsentDaysAmount'];
                $PayrollMasterDetail[$i]['LateEarlyExit']=$PayrollMasterDetail[$i]['LateEarlyExit'];
                $PayrollMasterDetail[$i]['LateEarlyExitDeduction']=$PayrollMasterDetail[$i]['LateEarlyExitDeduction'];
                $PayrollMasterDetail[$i]['ContractStartDate']=$PayrollMasterDetail[$i]['ContractStartDate'];
                $PayrollMasterDetail[$i]['ContractEndDate']=$PayrollMasterDetail[$i]['ContractEndDate'];

                if (strtolower($PayrollMasterDetail[$i]['ModeOfPayment'])== "bank")
                {

                    $salarySetting = EmployeeSalarySetup::query()->where('employee_id',$empId)->first();

                        if ($salarySetting != null)
                        {
                            $PayrollMasterDetail[$i]['BankAccountNo'] = $salarySetting->bankAccountNumber;
                            $PayrollMasterDetail[$i]['SalaryAccID'] = $salarySetting->SalaryAccID;
                            $PayrollMasterDetail[$i]['AdvanceAccID'] = $salarySetting->AdvanceAccID;
                            $PayrollMasterDetail[$i]['BankID'] = $salarySetting->bankId;
                        }
                }

                $MasterPayrollDetail=EmployeePayrollDetail::query()->create($PayrollMasterDetail[$i]);

                foreach($employeeAllowanceDeduction as $empAllowDeduction){


                     $value = 0.0;
                     $remarks = null;
                    if ($empAllowDeduction['description'] == "Other Allowance") // Check if Other Allowance or Other Deduction is configured in Employee_SalaryAllowances
                    {
                        if ($OtherAllowanceDeduction[$i]['OtherAllowance']) // Check if Other Allowance textbox has some value or its empty
                        {

                            $value = (double)$OtherAllowanceDeduction[$i]['OtherAllowance'];
                        }
                        $remarks = $OtherAllowanceDeduction[$i]['OtherAllowanceRemarks'];
                    }
                    else if ($empAllowDeduction['description'] == "Other Deduction")
                    {
                        if ($OtherAllowanceDeduction[$i]['OtherDeduction'])  // Check if Other Deduction textbox has some value or its empty
                        {

                            $value = (double)$OtherAllowanceDeduction[$i]['OtherDeduction'];
                        }

                        $remarks = $OtherAllowanceDeduction[$i]['OtherDeductionRemarks'];
                    }
                    else
                    {

                        $value = $empAllowDeduction['value'];
                    }

                    $Salary_AllowanceDeduction=[];
                    $Salary_AllowanceDeduction['PayrollDetailId'] = $MasterPayrollDetail->id;
                    $Salary_AllowanceDeduction['EmployeeId'] = $PayrollMasterDetail[$i]['EmployeeID'];
                    $Salary_AllowanceDeduction['Category'] = $empAllowDeduction['category'];
                    $Salary_AllowanceDeduction['Description'] = $empAllowDeduction['description'];
                    $Salary_AllowanceDeduction['Value'] = $value;
                    $Salary_AllowanceDeduction['AllowedLiters'] = $empAllowDeduction['allowed_liters'];
                    $Salary_AllowanceDeduction['PricePerLiter'] = $empAllowDeduction['price_per_liter'];
                    $Salary_AllowanceDeduction['EmployerShareValue'] = $empAllowDeduction['employee_share_value'];
                    $Salary_AllowanceDeduction['CalculatedBy'] = $empAllowDeduction['calculated_by'];
                    $Salary_AllowanceDeduction['EmployerShareCalculatedBy'] = ($empAllowDeduction['employee_share_calculated_by'] != "")?$empAllowDeduction['employee_share_calculated_by']:2;
                    $Salary_AllowanceDeduction['IsActive'] = true;
                    $Salary_AllowanceDeduction['IsDelete'] = false;
                    $Salary_AllowanceDeduction['IsTaxable'] = $empAllowDeduction['isTaxable'];
                    $Salary_AllowanceDeduction['DateCreated'] = date('Y-m-d h:i:s');
                    $Salary_AllowanceDeduction['Remarks'] = $remarks;

                    // if Allowances or Deductions are to be calcuated by %age then they would be handled here
                    if ($empAllowDeduction['calculated_by'] == 1) // 1 means Percentage
                    {

                        $Salary_AllowanceDeduction['Value'] = ((double)$monthlySalary * $value) / 100.00;
                        $Salary_AllowanceDeduction['CalculatedBy'] = 2; // Because we have calculated %age therefore now update the calculated by field.

                    }
                    if ($empAllowDeduction['employee_share_calculated_by'] == 1) // 1 means Percentage
                    {
                        $Salary_AllowanceDeduction['EmployerShareValue'] = ((double)$monthlySalary * $Salary_AllowanceDeduction['EmployerShareValue']) / 100.00;
                        $Salary_AllowanceDeduction['CalculatedBy'] = 2; // Because we have calculated %age therefore now update the calculated by field.
                    }
                    if ($empAllowDeduction['isTaxable'] == true)
                    {
                        if ($empAllowDeduction['calculated_by'] == 1)
                        {
                            if ($empAllowDeduction['description'] == "Other Allowance")
                            {
                                $taxableAllowances = $taxableAllowances + (float)$Salary_AllowanceDeduction['Value']; // keep taxableOtherAllowance as separate from other allownces.
                            }
                            else
                            {
                                $taxableAllowances = $taxableAllowances + (float)$Salary_AllowanceDeduction['Value']; // Tax is only applicable on employee share
                            }
                        }
                        else
                        {
                            if ($empAllowDeduction['description'] == "Other Deduction")
                            {
                                $taxableDeductions = $taxableDeductions + ((float)$Salary_AllowanceDeduction['Value']);
                            }
                            else
                            {
                                $taxableDeductions = $taxableDeductions + ((float)$Salary_AllowanceDeduction['Value']);
                            }
                        }
                    }

                    $Salary_AllowanceDeduction['Value'] = round($Salary_AllowanceDeduction['Value'], 0, PHP_ROUND_HALF_UP);
                    $Salary_AllowanceDeduction['EmployerShareValue'] = round($Salary_AllowanceDeduction['EmployerShareValue'] ?? 0.0, 0, PHP_ROUND_HALF_UP);



                    SalaryAllowanceDeduction::query()->create($Salary_AllowanceDeduction);
                }
                // Loan Settlement
                $PayPeriodFrom = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayPeriodFrom']));
                $PayPeriodTo = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayPeriodTo']));
                $PayMonth = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayMonth']));
                $start_date=null;
                $end_date=null;
                if($PayMonth){
                    $start_date=date('m-01-Y',strtotime($PayMonth));
                    $end_date=date('m-t-Y',strtotime($PayMonth));
                }else{
                    $start_date=date('m-01-Y',strtotime($PayPeriodFrom));
                    $end_date=date('m-t-Y',strtotime($PayPeriodTo));
                }
                $loanAdvanceDetail=AdvanceSalaryInstallment::query()->where('employee_id', $PayrollMasterDetail[$i]['EmployeeID'])->whereBetween('due_date',[$start_date,$end_date])->first();


                if($loanAdvanceDetail && (float)$MasterPayrollDetail->LoanInstalment > 0){
                    // Add Loan Installmenti in Loan Settlement Screen
                    $LoanSettlement=[];
                    $LoanSettlement['PayrollDetailId'] = $MasterPayrollDetail->id;
                    $LoanSettlement['Amount'] = $loanAdvanceDetail->due_amount;
                    $LoanSettlement['InstallmentNumber'] = $loanAdvanceDetail->installment_no;
                    $LoanSettlement['LoanAdvancesId'] = $loanAdvanceDetail->advance_salary_id;
                    $LoanSettlement['ReferenceId'] = $loanAdvanceDetail->id;
                    $LoanSettlement['EmployeeId'] = $MasterPayrollDetail->EmployeeId;
                    $LoanSettlement['PayVia'] = "Payroll Bulk";

                    LoanSettlement::query()->create($LoanSettlement);

                    // Add installment in payroll as deduction

                    $loanDeduction=[];
                    $loanDeduction['PayrollDetailId'] = $MasterPayrollDetail->id;
                    $loanDeduction['Description'] = "Loan Installment";
                    $loanDeduction['Value'] = (float)$loanAdvanceDetail->due_amount;
                    $loanDeduction['Category'] = 2; // Deduction
                    $loanDeduction['CalculatedBy'] = 2;
                    $loanDeduction['EmployerShareCalculatedBy'] = 2;
                    $loanDeduction['EmployerShareValue'] = null;
                    $loanDeduction['EmployeeId'] = $MasterPayrollDetail->EmployeeId;
                    $loanDeduction['IsDelete'] = false;
                    $loanDeduction['IsActive'] = true;
                    $loanDeduction['IsTaxable'] = false;
                    SalaryAllowanceDeduction::query()->create($loanDeduction);
                    $loanAdvanceDetail->PayrollDetailId=$MasterPayrollDetail->id;
                    $loanAdvanceDetail->save();


                    $employeeLedgerForLoanInstallment=[];
                    $employeeLedgerForLoanInstallment['PayrollDetailId'] = $MasterPayrollDetail->id;
                    $employeeLedgerForLoanInstallment['EmployeeLedgerNumber'] = $employeeLedgerNumber++;
                    $employeeLedgerForLoanInstallment['EmployeeId'] = $MasterPayrollDetail->EmployeeId;
                    $employeeLedgerForLoanInstallment['ReferenceId'] = $loanAdvanceDetail->id;
                    $employeeLedgerForLoanInstallment['VoucherType'] = 'INS'; // Deduction
                    $employeeLedgerForLoanInstallment['Description'] = "Loan Installment Paid (Loan Id: " .$loanAdvanceDetail->advance_salary_id . ")";
                    $employeeLedgerForLoanInstallment['TransactionDate'] =$MasterPayrollDetail->PaymentDate;
                    $employeeLedgerForLoanInstallment['PayMonth'] = $MasterPayrollDetail->PayMonth;
                    $employeeLedgerForLoanInstallment['Debit'] = 0;
                    $employeeLedgerForLoanInstallment['Credit'] = (float)$loanAdvanceDetail->due_amount;
                    EmployeeLedger::query()->create($employeeLedgerForLoanInstallment);
                }



                if($employeeSalarySegregation->salarySegregation){
                    foreach($employeeSalarySegregation->salarySegregation as $Segregation){

                        $inserSalrySegregation=array(
                            'PayrollDetailId'=>$MasterPayrollDetail->id,
                            'SalaryPercentage'=>$Segregation['salary_percentage'],
                            'ProjectId'=>$Segregation['project_id'],
                            'EmpSalarySegregationId'=>$Segregation['id'],
                            'SalaryAmount'=>($Segregation['salary_percentage'] / 100) * $NetPay,

                        );
                        EmployeePayrollSegregation::query()->create($inserSalrySegregation);

                    }
                }

                $empPreGrossSalary=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
                if($empPreGrossSalary){
                    foreach($empPreGrossSalary as $preGrossSalary){

                        $inserpreGrossSalrySegregation=array(
                            'PayrollDetailId'=>$MasterPayrollDetail->id,
                            'PreGrossSalaryId'=>$preGrossSalary['id'],
                            'PreGrossAllowanceName'=>$preGrossSalary->allowanceType->name,
                            'PreGrossPercentage'=>$preGrossSalary['allowance_percentage'],
                            'PreGrossSalaryAmount'=>($preGrossSalary['allowance_percentage'] / 100) * $NetPay,

                        );
                        EmployeePayrollPreGrossSalary::query()->create($inserpreGrossSalrySegregation);

                    }
                }


                // Start Payroll Tax Calculation
                $PayPeriodFrom = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayPeriodFrom']));
                $PayPeriodTo = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayPeriodTo']));
                $PayMonth = date('Y-m-d',strtotime( $PayrollMasterDetail[$i]['PayMonth']));

                $dateStartObj = isset($payrollDetailList[$i]['PayMonth']) ?
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayMonth)) :
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayPeriodFrom));



                $dateEndObj = isset($payrollDetailList[$i]['PayMonth']) ?
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayMonth)) :
                    \Carbon\Carbon::createFromFormat('Y-m-d',  trim($PayPeriodTo));


                if ($dateStartObj->month >= 7) {
                    // Jul - Dec
                    $startYear = $dateStartObj->year;
                } else {
                    // Jan - Jun
                    $startYear = $dateStartObj->year - 1;
                }


                if ($dateEndObj->month >= 7) {
                    // Jul - Dec
                    $endYear = $dateEndObj->year + 1;
                } else {
                    // Jan - Jun
                    $endYear = $dateEndObj->year;
                }


                $fromDate = \Carbon\Carbon::create($startYear, 7, 1, 0, 0, 0);
                $toDate = \Carbon\Carbon::create($endYear, 6, 30, 23, 59, 59);

                $dateStart = $dateStartObj->format('Y-m-d');
                $dateEnd = $dateEndObj->format('Y-m-d');
                $fromDate = $fromDate->format('Y-m-d h:i:s');
                $toDate = $toDate->format('Y-m-d h:i:s');


                $result = DB::select('EXEC [dbo].[usp_GetPaidAmount] ?, ?, ?, ?', [
                    $empId,      // Parameter 1
                    $fromDate,      // Parameter 2
                    $toDate,   // Parameter 3
                    $dateStart,   // Parameter 4
                ]);




                // Past Month
                $paidSalary = $result[0]->PaidNetSalary + $result[0]->PaidTaxableAllowances + $result[0]->PaidTaxableDeductions - $monthlySalary;

                // Future Month
                $futureTaxableSalary = ($monthlySalary + $taxableAllowances + $taxableDeductions) * (12 - ($result[0]->TotalMonths + 1));

                // Current Month
                $currentTaxableSalary = $PayrollMasterDetail[$i]['NetSalary'] + $taxableAllowances + $taxableDeductions + ($taxableOtherAllowance + $taxableOtherDeduction);

                $taxableSalary = $paidSalary - $result[0]->PaidLeaveEncashment + $currentTaxableSalary + $futureTaxableSalary;



                $medicalExemption = ((($taxableSalary + $taxableOtherAllowance) - (($PayrollMasterDetail[$i]['DailySalary'] * (float) $PayrollMasterDetail[$i]['Absences']))) / 110.0) * 10.0; // 9.10% medical exemption on annual monthly salary and taxable allowance deductions

                $taxableSalary = $taxableSalary - $medicalExemption;


                $totalTax = 0.0;

                if ($dateStartObj->month != 6) // for 11 months (July-May) deduct tax only if it is applicable
                {


                    $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);

                    if ($tax != null)
                    {
                        $exceedingAmount = $taxableSalary - $tax['salary_from'];
                        $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));
                        if ($totalTax < $tax['minimum_tax_amount'])
                        {
                            $totalTax = $tax['minimum_tax_amount'];
                        }

                        $totalTax = $totalTax - $result[0]->PaidTax;

                        if ($result[0]->TotalMonths != 12)
                        {
                            $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                        }
                    }


                }
                else // for 12th months (June) tax deduction will be compulsory
                {
                    //// Last month main bonus ki forecast nai dalni taxable salary main.
                    //var remainingForecastBonus = (monthlySalary * 2) - result.PaidBonusAmount;
                    //taxableSalary = taxableSalary - remainingForecastBonus;

                    $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);
                    if ($tax != null)
                    {
                        $exceedingAmount = $taxableSalary - $tax['salary_from'];


                        $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));

                        if ($totalTax < $tax['minimum_tax_amount'])
                        {
                            $totalTax = $tax['minimum_tax_amount'];
                        }
                        $totalTax = $totalTax - $result[0]->PaidTax;

                        if ($result[0]->TotalMonths != 12)
                        {
                            $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                        }
                    }
                }
                if ($totalTax < 0)
                {
                    $totalTax = 0.0;
                }

                // End Payroll Tax Calculation
                // Add Tax as a deduction in both, normal salaries and bonus salaries
                $tax_Deduction=[];
                $tax_Deduction['PayrollDetailId'] = $MasterPayrollDetail->id;
                $tax_Deduction['EmployeeId'] = $PayrollMasterDetail[$i]['EmployeeID'];
                $tax_Deduction['Category'] = 2;
                $tax_Deduction['Description'] = "TAX";
                $tax_Deduction['Value'] = $totalTax = round($totalTax);
/*              $tax_Deduction['AllowedLiters'] = NULL;
                $tax_Deduction['PricePerLiter'] = NULL;
                $tax_Deduction['EmployerShareValue'] = NULL;*/
                $tax_Deduction['CalculatedBy'] = 2;
                $tax_Deduction['EmployerShareCalculatedBy'] = 2;
                $tax_Deduction['IsActive'] = true;
                $tax_Deduction['IsDelete'] = false;
                $tax_Deduction['IsTaxable'] = false;
                $tax_Deduction['DateCreated'] = date('Y-m-d h:i:s');
                SalaryAllowanceDeduction::query()->create($tax_Deduction);

                // Debit
                $employeeLedgerSalaryDebit=[];
                $employeeLedgerSalaryDebit['PayrollDetailId'] = $MasterPayrollDetail->id;
                $employeeLedgerSalaryDebit['EmployeeLedgerNumber'] = $employeeLedgerNumber;
                $employeeLedgerSalaryDebit['EmployeeId'] = $MasterPayrollDetail->EmployeeId;
                $employeeLedgerSalaryDebit['VoucherType'] = 'SAL'; // Deduction
                $employeeLedgerSalaryDebit['Description'] = "Salary Paid";
                $employeeLedgerSalaryDebit['TransactionDate'] =$MasterPayrollDetail->PaymentDate;
                $employeeLedgerSalaryDebit['PayMonth'] = $MasterPayrollDetail->PayMonth;
                $employeeLedgerSalaryDebit['Debit'] = (float)$MasterPayrollDetail->NetPay;
                $employeeLedgerSalaryDebit['Credit'] = 0;
                EmployeeLedger::query()->create($employeeLedgerSalaryDebit);

                // Credit
                $employeeLedgerSalaryCredit=[];
                $employeeLedgerSalaryCredit['PayrollDetailId'] = $MasterPayrollDetail->id;
                $employeeLedgerSalaryCredit['EmployeeLedgerNumber'] = $employeeLedgerNumber;
                $employeeLedgerSalaryCredit['EmployeeId'] = $MasterPayrollDetail->EmployeeId;
                $employeeLedgerSalaryCredit['VoucherType'] = 'SAL'; // Deduction
                $employeeLedgerSalaryCredit['Description'] = "Salary Paid";
                $employeeLedgerSalaryCredit['TransactionDate'] =$MasterPayrollDetail->PaymentDate;
                $employeeLedgerSalaryCredit['PayMonth'] = $MasterPayrollDetail->PayMonth;
                $employeeLedgerSalaryCredit['Debit'] = 0;
                $employeeLedgerSalaryCredit['Credit'] = (float)$MasterPayrollDetail->NetPay;
                EmployeeLedger::query()->create($employeeLedgerSalaryCredit);

                $employeeLedgerNumber += 1;


            }


            DB::commit();
            return resp('1', 'Payroll added Successfully!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function GetTax($dateStart, $dateEnd, $taxableSalary)
    {
        $taxRate=PayrollTaxRates::query()->where('financial_year_start_date','<=',$dateStart)->where('financial_year_end_date','>=',$dateEnd)->where('salary_from','<=',$taxableSalary)->where('salary_to', '>=',$taxableSalary)->where('isActive',1)->first();
        $taxRateArray = $taxRate ? $taxRate->toArray() : [];
         return $taxRateArray;

    }
    public function sendPayrollForApproval(EmployeePayrollMaster $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',31)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',31)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            EmployeePayrollMaster::query()->where('id',$item->id)->update($update);
            return resp(1,'Payroll send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Payroll approval already sent.', [],Response::HTTP_OK);
            }
        }
    }

    public function getEmployeePayroll(Request $request)
    {
        $this->authorizeAny([
            'manage_employee_portal',
            'manage_payslip',
            'manage_audit_payroll',
        ]);

        $request->validate([
            'employee_id' => 'required',
            'pay_month' => 'required',
        ]);

        try {
            $data['employee_salary']=EmployeePayrollDetail::query()->with(['salaryAllownaceDeduction','salarySegregation.projectDetail','BankDetail','preGrossSalaryAllowances','employeeDetail' => ['designation','grade','department','branchOffice','district']])->where(['EmployeeId'=>$request->employee_id, 'PayMonth' => $request->pay_month])->first();

            return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deletePayroll(Request $request)
    {
        $this->authorizeAny([
            'manage_employee_portal',
            'manage_payslip',
            'manage_audit_payroll',
        ]);

        $request->validate([
            'payroll_id' => 'required',
        ]);

        try {

            DB::beginTransaction();
            $payroll_id=$request->payroll_id;
            $payrollDetailids = DB::table('employee_payroll_details')
                ->where('PayrollMasterId', $payroll_id)
                ->pluck('id')
                ->toArray();

            // Delete records from related tables using DB facade
            DB::table('loan_settlements')->whereIn('PayrollDetailId', $payrollDetailids)->delete();
            DB::table('employee_payroll_segregations')->whereIn('PayrollDetailId', $payrollDetailids)->delete();
            DB::table('employee_payroll_pre_gross_salaries')->whereIn('PayrollDetailId', $payrollDetailids)->delete();
            DB::table('salary_allowance_deductions')->whereIn('PayrollDetailId', $payrollDetailids)->delete();
            DB::table('employee_ledgers')->whereIn('PayrollDetailId', $payrollDetailids)->delete();
            DB::table('employee_payroll_details')->where('PayrollMasterId', $payroll_id)->delete();
            DB::table('employee_payroll_masters')->where('id', $payroll_id)->delete();

            DB::commit();
            return resp('1', 'Payroll deleted Successfully!', [], Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function payrollCheck(Request $request)
    {
        $this->authorizeAny([
            'manage_single_payroll',
            'manage_audit_payroll',
        ]);

        $request->validate([
            'employee_id' => 'required',
        ]);

        try {
            $monthlySalary = 0.0;
            $taxableAllowances = 0.0;
            $taxableDeductions = 0.0;
            $taxableSalary = 0.0;
            $paidSalary = 0.0;
            $futureTaxableSalary = 0.0;
            $currentTaxableSalary = 0.0;
            $medicalExemption = 0.0;
            $previousSalary = 0.0;
            $salary = EmployeeSalarySetup::query()->where('employee_id',$request->employee_id)->first();

            if($salary){


                    $empsalaryPreGrossSalary=PreGrossSalaryAllowances::query()->with('allowanceType')->get();

                    foreach($empsalaryPreGrossSalary as $pkey => $preGross){

                        $inserPreGrossSalrySegregation[$pkey]=array(
                            'allowance_percentage'=>$preGross['allowance_percentage'],
                            'allowance_name'=>$preGross->allowanceType->name,
                            'salary_amount'=>($preGross['allowance_percentage'] / 100) * $salary->monthly_salary,
                        );

                    }
                $salary['PreGrossSalrySegregation']=$inserPreGrossSalrySegregation;

            }
            $payrollDetId = 0;
            $rowNum = 0;
            if ( !empty($salary) && $salary != null)
            {
                $monthlySalary = $salary->monthly_salary;
            }

            $dateStart = Carbon::now();
            $dateEnd = $dateStart->copy()->addMonth()->subSecond();
            $salaryMonth = Carbon::now();
            $startYear = 0;
            $endYear = 0;





            if ($dateStart->month >= 7) {
                // Jul - Dec
                $startYear = $dateStart->year;
            } else {
                // Jan - Jun
                $startYear = $dateStart->year - 1;
            }


            if ($dateEnd->month >= 7) {
                // Jul - Dec
                $endYear = $dateEnd->year + 1;
            } else {
                // Jan - Jun
                $endYear = $dateEnd->year;
            }


            $startDate = \Carbon\Carbon::create($startYear, 7, 1, 0, 0, 0);
            $endDate = \Carbon\Carbon::create($endYear, 6, 30, 23, 59, 59);

            $fromDate = $startDate->format('Y-m-d h:i:s');
            $toDate = $endDate->format('Y-m-d h:i:s');
            $flag = false;
            $decrementFlag = false;

            $employeeAllowances=EmployeeAllowanceDeduction::query()->where('employee_id',$request->employee_id)->get();
            /*dd($employeeAllowances->toArray());*/
                $previousSum = 0.0;
                $nextSum = 0.0;
                $difference=0;
                $counter = 0;
                $months = 0;
                $currVal=0;
                $prevVal=0;
            for ($i = 0; $i < count($employeeAllowances); $i++)
            {
                $difference = 0.0;

                if ($employeeAllowances[$i]['calculated_by'] == 1) // && employeeAllowances[i].Description == "Provident Fund") // 1 means Percentage
                {
                    $employeeAllowances[$i]['value'] = $difference + (($monthlySalary * $employeeAllowances[$i]['value']) / 100.00);
                    $employeeAllowances[$i]['calculated_by'] = 2; // Because we have calculated %age therefore now update the calculated by field.
                }
                if ($employeeAllowances[$i]['employee_share_calculated_by'] == 1)// && employeeAllowances[i].Description == "Provident Fund") // 1 means Percentage
                {
                    $employeeAllowances[$i]['employee_share_value'] = $difference + ((double)$monthlySalary * $employeeAllowances[$i]['employee_share_value']) / 100.00 ;
                        $employeeAllowances[$i]['employee_share_calculated_by'] = 2; // Because we have calculated %age therefore now update the calculated by field.
                }

                // Tax is not applicable on deductions
                if ($employeeAllowances[$i]['isTaxable'] == true)
                {
                    if ($employeeAllowances[$i]['category'] == 1) // Allowances
                    {
                        $taxableAllowances = $taxableAllowances + (float)$employeeAllowances[$i]['value'];
                    }
                    else // Deductions
                    {

                        $taxableDeductions = $taxableDeductions + ((float)$employeeAllowances[$i]['value']);
                    }
                }
            }

            $empId = $request->employee_id;
            $result = DB::select('EXEC [dbo].[usp_GetPaidAmount] ?, ?, ?, ?', [
                $empId,      // Parameter 1
                $fromDate,      // Parameter 2
                $toDate,   // Parameter 3
                $dateStart,   // Parameter 4
            ]);

            // Past Month
            $paidSalary = $result[0]->PaidNetSalary + $result[0]->PaidTaxableAllowances + $result[0]->PaidTaxableDeductions;

            // Future Month
            $futureTaxableSalary = ($monthlySalary + $taxableAllowances + $taxableDeductions) * (12 - ($result[0]->TotalMonths + 1));

            // Current Month
            $currentTaxableSalary = $monthlySalary + $taxableAllowances + $taxableDeductions;

            $taxableSalary = $paidSalary - $result[0]->PaidLeaveEncashment + $currentTaxableSalary + $futureTaxableSalary;




            //$medicalExemption = ((($taxableSalary + $taxableOtherAllowance) - (($PayrollMasterDetail[$i]['DailySalary'] * (float) $PayrollMasterDetail[$i]['Absences']))) / 110.0) * 10.0; // 9.10% medical exemption on annual monthly salary and taxable allowance deductions
            $medicalExemption = (($taxableSalary) / 110.0) * 10.0;

            $taxableSalary = $taxableSalary - $medicalExemption;

            $totalTax = 0.0;

            if ($dateStart->month != 6) // for 11 months (July-May) deduct tax only if it is applicable
            {
                $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);

                if ($tax != null)
                {
                    $exceedingAmount = $taxableSalary - $tax['salary_from'];
                    $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));
                    if ($totalTax < $tax['minimum_tax_amount'])
                    {
                        $totalTax = $tax['minimum_tax_amount'];
                    }

                    $totalTax = $totalTax - $result[0]->PaidTax;

                    if ($result[0]->TotalMonths != 12)
                    {
                        $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                    }
                }
            }else{
                $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);
                if ($tax != null)
                {
                    $exceedingAmount = $taxableSalary - $tax['salary_from'];


                    $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));

                    if ($totalTax < $tax['minimum_tax_amount'])
                    {
                        $totalTax = $tax['minimum_tax_amount'];
                    }
                    $totalTax = $totalTax - $result[0]->PaidTax;

                    if ($result[0]->TotalMonths != 12)
                    {
                        $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                    }
                }
            }
            if ($totalTax < 0)
            {
                $totalTax = 0.0;
            }

            $tax_Deduction=[];
            $tax_Deduction['EmployeeId'] =$empId;
            $tax_Deduction['Category'] = 2;
            $tax_Deduction['Description'] = "TAX";
            $tax_Deduction['Value'] = $totalTax = round($totalTax);
            $tax_Deduction['CalculatedBy'] = 2;
            $tax_Deduction['EmployerShareCalculatedBy'] = 2;
            $tax_Deduction['IsActive'] = true;
            $tax_Deduction['IsDelete'] = false;
            $tax_Deduction['IsTaxable'] = false;
            $tax_Deduction['DateCreated'] = date('Y-m-d h:i:s');
            $employeeAllowances[] = $tax_Deduction;
            $data['employeeAllowances']=$employeeAllowances;
            $data['employee_salary'] = $salary;
            $data['employee_detail'] = Employee::query()->with('empContract.employeeContractDetail','grade')->where('id',$empId)->first();
            //$data['unpaid_installment'] = Employee::query()->with('empContract.employeeContractDetail','grade')->where('id',$empId)->first();
            return resp('1', 'Successfully!', $data, Response::HTTP_CREATED);


        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createPayrollSingle(Request $request)
    {
        $this->authorizeAny([
            'manage_single_payroll',
        ]);

        try {
            DB::beginTransaction();
            $request->validate([
                'PayrollMaster.PaymentDate' => 'required|date',
                'PayrollMaster.TotalMonthlySalary' => 'required|numeric|min:0',
                'PayrollMaster.TotalNetSalary' => 'required|numeric|min:0',
                'PayrollMaster.TotalDeductions' => 'required|numeric|min:0',
                'PayrollMaster.TotalAllowances' => 'required|numeric|min:0',
                'PayrollMaster.TotalNetPay' => 'required|numeric|min:0',
            ]);


            $PayrollMaster=$request->PayrollMaster;
            $PayrollMasterDetail=$request->PayrollMasterDetail;
            $OtherAllowanceDeduction=$request->AllowanceDeduction;

            if (!$PayrollMasterDetail['IsBonusSalary']) // Normal Salary
            {
                $year = 0;
                $month = 0;

                if (!empty($PayrollMasterDetail['PayMonth'])) {
                    $payMonth = Carbon::parse($PayrollMasterDetail['PayMonth']);
                    $year = $payMonth->year;
                    $month = $payMonth->month;
                } else {
                    $payPeriodFrom = Carbon::parse($PayrollMasterDetail['PayPeriodFrom']);
                    $year = $payPeriodFrom->year;
                    $month = $payPeriodFrom->month;
                }

                $empPayrollDetail = $this->getPayrollDetail($PayrollMasterDetail['IsBonusSalary'],
                    $PayrollMasterDetail['EmployeeID'],$year, $month);

                if (!empty($empPayrollDetail) && $empPayrollDetail != null)
                {
                    return resp('0', 'Duplicate salary is not allowed' , null, Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
            $PayrollMaster['PaymentDate']=date('Y-m-d',strtotime( $PayrollMaster['PaymentDate']));
            $PayrollMaster['PayPeriodFrom']=(@$PayrollMaster['PayPeriodFrom'])?date('Y-m-d',strtotime( $PayrollMaster['PayPeriodFrom'])):'';
            $PayrollMaster['PayPeriodTo']=(@$PayrollMaster['PayPeriodTo'])?date('Y-m-d',strtotime( $PayrollMaster['PayPeriodTo'])):'';
            $PayrollMaster['DateCreated']=date('Y-m-d h:i:s');
            $PayrollMasterDetail['DateCreated']=date('Y-m-d h:i:s');
            $PayrollMasterDetail['GeneratedVia']="Single";
            $MasterPayroll=EmployeePayrollMaster::query()->create($PayrollMaster);
            if($MasterPayroll)
            {

                $monthlySalary = $PayrollMasterDetail['MonthlySalary'] ?? 0.0;
                $netSalary = $PayrollMasterDetail['NetSalary'] ?? 0.0;
                $NetPay = $PayrollMasterDetail['NetPay'] ?? 0.0;
                $taxableAllowances = 0.0;
                $taxableDeductions = 0.0;
                $taxableOtherAllowance = 0.0;
                $taxableOtherDeduction = 0.0;
                $taxableSalary = 0.0;
                $paidSalary = 0.0;
                $futureTaxableSalary = 0.0;
                $currentTaxableSalary = 0.0;
                $medicalExemption = 0.0;

                $empId = $PayrollMasterDetail['EmployeeID'];

                $employeeAllowanceDeduction = EmployeeAllowanceDeduction::query()->where('employee_id',$empId)->get();
                $employeeSalarySegregation=EmployeeSalarySetup::query()->with('salarySegregation.projectDetail')->where('employee_id',$empId)->select('id','employee_id')->first();
                $PayrollMasterDetail['PaymentDate']=date('Y-m-d',strtotime($PayrollMasterDetail['PaymentDate']));
                $PayrollMasterDetail['PayPeriodFrom']=(@$PayrollMasterDetail['PayPeriodFrom'])?date('Y-m-d',strtotime($PayrollMasterDetail['PayPeriodFrom'])):'';
                $PayrollMasterDetail['PayPeriodTo']=(@$PayrollMasterDetail['PayPeriodTo'])?date('Y-m-d',strtotime($PayrollMasterDetail['PayPeriodTo'])):'';
                $PayrollMasterDetail['DateCreated']=date('Y-m-d h:i:s');
                $PayrollMasterDetail['GeneratedVia']='Single';
                $PayrollMasterDetail['created_by']=auth()->user()->id;
                $PayrollMasterDetail['PayrollMasterId']=$MasterPayroll->id;
                $PayrollMasterDetail['EmployeeId']=$empId;
                $PayrollMasterDetail[$i]['arrears']=$PayrollMasterDetail[$i]['emp_arrears'];
                $PayrollMasterDetail[$i]['tax']=$PayrollMasterDetail[$i]['TAX'];
                $PayrollMasterDetail[$i]['EOBI']=$PayrollMasterDetail[$i]['EOBIDeductions'];
                $PayrollMasterDetail[$i]['unpaidInstallment']=$PayrollMasterDetail[$i]['UnpaidInstallment'];
                $PayrollMasterDetail[$i]['allowance']=$PayrollMasterDetail[$i]['Allowances'];
                $PayrollMasterDetail[$i]['deduction']=$PayrollMasterDetail[$i]['Deductions'];
                $PayrollMasterDetail[$i]['remarks']=$PayrollMasterDetail[$i]['emp_remarks'];
                $PayrollMasterDetail[$i]['AbsentWOLeave']=$PayrollMasterDetail[$i]['AbsentWOLeave'];
                $PayrollMasterDetail[$i]['AbsentDaysAmount']=$PayrollMasterDetail[$i]['AbsentDaysAmount'];
                $PayrollMasterDetail[$i]['LateEarlyExit']=$PayrollMasterDetail[$i]['LateEarlyExit'];
                $PayrollMasterDetail[$i]['LateEarlyExitDeduction']=$PayrollMasterDetail[$i]['LateEarlyExitDeduction'];

                if (strtolower($PayrollMasterDetail['ModeOfPayment'])== "bank")
                {

                    $salarySetting = EmployeeSalarySetup::query()->where('employee_id',$empId)->first();

                    if ($salarySetting != null)
                    {
                        $PayrollMasterDetail['BankAccountNo'] = $salarySetting->bankAccountNumber;
                        $PayrollMasterDetail['SalaryAccID'] = $salarySetting->SalaryAccID;
                        $PayrollMasterDetail['AdvanceAccID'] = $salarySetting->AdvanceAccID;
                        $PayrollMasterDetail['BankID'] = $salarySetting->bankId;
                    }
                }

                $MasterPayrollDetail=EmployeePayrollDetail::query()->create($PayrollMasterDetail);

                foreach($employeeAllowanceDeduction as $empAllowDeduction){

                    $value = 0.0;
                    $remarks = null;
                    if ($empAllowDeduction['description'] == "Other Allowance") // Check if Other Allowance or Other Deduction is configured in Employee_SalaryAllowances
                    {
                        if ($OtherAllowanceDeduction['OtherAllowance']) // Check if Other Allowance textbox has some value or its empty
                        {

                            $value = (double)$OtherAllowanceDeduction['OtherAllowance'];
                        }
                        $remarks = $OtherAllowanceDeduction['OtherAllowanceRemarks'];
                    }
                    else if ($empAllowDeduction['description'] == "Other Deduction")
                    {
                        if ($OtherAllowanceDeduction['OtherDeduction'])  // Check if Other Deduction textbox has some value or its empty
                        {

                            $value = (double)$OtherAllowanceDeduction['OtherDeduction'];
                        }

                        $remarks = $OtherAllowanceDeduction['OtherDeductionRemarks'];
                    }
                    else
                    {

                        $value = $empAllowDeduction['value'];
                    }

                    $Salary_AllowanceDeduction=[];
                    $Salary_AllowanceDeduction['PayrollDetailId'] = $MasterPayrollDetail->id;
                    $Salary_AllowanceDeduction['EmployeeId'] = $PayrollMasterDetail['EmployeeID'];
                    $Salary_AllowanceDeduction['Category'] = $empAllowDeduction['category'];
                    $Salary_AllowanceDeduction['Description'] = $empAllowDeduction['description'];
                    $Salary_AllowanceDeduction['Value'] = $value;
                    $Salary_AllowanceDeduction['AllowedLiters'] = $empAllowDeduction['allowed_liters'];
                    $Salary_AllowanceDeduction['PricePerLiter'] = $empAllowDeduction['price_per_liter'];
                    $Salary_AllowanceDeduction['EmployerShareValue'] = $empAllowDeduction['employee_share_value'];
                    $Salary_AllowanceDeduction['CalculatedBy'] = $empAllowDeduction['calculated_by'];
                    $Salary_AllowanceDeduction['EmployerShareCalculatedBy'] = ($empAllowDeduction['employee_share_calculated_by'] != "")?$empAllowDeduction['employee_share_calculated_by']:2;
                    $Salary_AllowanceDeduction['IsActive'] = true;
                    $Salary_AllowanceDeduction['IsDelete'] = false;
                    $Salary_AllowanceDeduction['IsTaxable'] = $empAllowDeduction['isTaxable'];
                    $Salary_AllowanceDeduction['DateCreated'] = date('Y-m-d h:i:s');
                    $Salary_AllowanceDeduction['Remarks'] = $remarks;
                    /*echo '<pre>';
                    print_r($Salary_AllowanceDeduction);*/
                    SalaryAllowanceDeduction::query()->create($Salary_AllowanceDeduction);
                }
                if($employeeSalarySegregation->salarySegregation){
                    foreach($employeeSalarySegregation->salarySegregation as $Segregation){

                        $inserSalrySegregation=array(
                            'PayrollDetailId'=>$MasterPayrollDetail->id,
                            'SalaryPercentage'=>$Segregation['salary_percentage'],
                            'ProjectId'=>$Segregation['project_id'],
                            'EmpSalarySegregationId'=>$Segregation['id'],
                            'SalaryAmount'=>($Segregation['salary_percentage'] / 100) * $NetPay,

                        );
                        EmployeePayrollSegregation::query()->create($inserSalrySegregation);

                    }
                }

                // Start Payroll Tax Calculation
                $PayPeriodFrom = date('Y-m-d',strtotime( $PayrollMasterDetail['PayPeriodFrom']));
                $PayPeriodTo = date('Y-m-d',strtotime( $PayrollMasterDetail['PayPeriodTo']));
                $PayMonth = date('Y-m-d',strtotime( $PayrollMasterDetail['PayMonth']));

                $dateStartObj = isset($payrollDetailList['PayMonth']) ?
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayMonth)) :
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayPeriodFrom));



                $dateEndObj = isset($payrollDetailList['PayMonth']) ?
                    \Carbon\Carbon::createFromFormat('Y-m-d', trim($PayMonth)) :
                    \Carbon\Carbon::createFromFormat('Y-m-d',  trim($PayPeriodTo));


                if ($dateStartObj->month >= 7) {
                    // Jul - Dec
                    $startYear = $dateStartObj->year;
                } else {
                    // Jan - Jun
                    $startYear = $dateStartObj->year - 1;
                }


                if ($dateEndObj->month >= 7) {
                    // Jul - Dec
                    $endYear = $dateEndObj->year + 1;
                } else {
                    // Jan - Jun
                    $endYear = $dateEndObj->year;
                }


                $fromDate = \Carbon\Carbon::create($startYear, 7, 1, 0, 0, 0);
                $toDate = \Carbon\Carbon::create($endYear, 6, 30, 23, 59, 59);

                $dateStart = $dateStartObj->format('Y-m-d');
                $dateEnd = $dateEndObj->format('Y-m-d');
                $fromDate = $fromDate->format('Y-m-d h:i:s');
                $toDate = $toDate->format('Y-m-d h:i:s');

                $result = DB::select('EXEC [dbo].[usp_GetPaidAmount] ?, ?, ?, ?', [
                    $empId,      // Parameter 1
                    $fromDate,      // Parameter 2
                    $toDate,   // Parameter 3
                    $dateStart,   // Parameter 4
                ]);

                // Past Month
                $paidSalary = $result[0]->PaidNetSalary + $result[0]->PaidTaxableAllowances + $result[0]->PaidTaxableDeductions;

                // Future Month
                $futureTaxableSalary = ($monthlySalary + $taxableAllowances + $taxableDeductions) * (12 - ($result[0]->TotalMonths + 1));

                // Current Month
                $currentTaxableSalary = $PayrollMasterDetail['NetSalary'] + $taxableAllowances + $taxableDeductions + ($taxableOtherAllowance + $taxableOtherDeduction);

                $taxableSalary = $paidSalary - $result[0]->PaidLeaveEncashment + $currentTaxableSalary + $futureTaxableSalary;



                $medicalExemption = ((($taxableSalary + $taxableOtherAllowance) - (($PayrollMasterDetail['DailySalary'] * (float) $PayrollMasterDetail['Absences']))) / 110.0) * 10.0; // 9.10% medical exemption on annual monthly salary and taxable allowance deductions

                $taxableSalary = $taxableSalary - $medicalExemption;

                $totalTax = 0.0;

                if ($dateStartObj->month != 6) // for 11 months (July-May) deduct tax only if it is applicable
                {


                    $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);

                    if ($tax != null)
                    {
                        $exceedingAmount = $taxableSalary - $tax['salary_from'];
                        $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));
                        if ($totalTax < $tax['minimum_tax_amount'])
                        {
                            $totalTax = $tax['minimum_tax_amount'];
                        }

                        $totalTax = $totalTax - $result[0]->PaidTax;

                        if ($result[0]->TotalMonths != 12)
                        {
                            $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                        }
                    }


                }
                else // for 12th months (June) tax deduction will be compulsory
                {
                    //// Last month main bonus ki forecast nai dalni taxable salary main.
                    //var remainingForecastBonus = (monthlySalary * 2) - result.PaidBonusAmount;
                    //taxableSalary = taxableSalary - remainingForecastBonus;

                    $tax = $this->GetTax($dateStart, $dateEnd, $taxableSalary);
                    if ($tax != null)
                    {
                        $exceedingAmount = $taxableSalary - $tax['salary_from'];


                        $totalTax = $tax['fixed_amount'] + ($exceedingAmount * ((float)$tax['tax_rate'] / 100.00));

                        if ($totalTax < $tax['minimum_tax_amount'])
                        {
                            $totalTax = $tax['minimum_tax_amount'];
                        }
                        $totalTax = $totalTax - $result[0]->PaidTax;

                        if ($result[0]->TotalMonths != 12)
                        {
                            $totalTax = $totalTax / (12 - $result[0]->TotalMonths);
                        }
                    }
                }
                if ($totalTax < 0)
                {
                    $totalTax = 0.0;
                }

                // End Payroll Tax Calculation
                // Add Tax as a deduction in both, normal salaries and bonus salaries
                $tax_Deduction=[];
                $tax_Deduction['PayrollDetailId'] = $MasterPayrollDetail->id;
                $tax_Deduction['EmployeeId'] = $PayrollMasterDetail['EmployeeID'];
                $tax_Deduction['Category'] = 2;
                $tax_Deduction['Description'] = "TAX";
                $tax_Deduction['Value'] = $totalTax = round($totalTax);
                $tax_Deduction['CalculatedBy'] = 2;
                $tax_Deduction['EmployerShareCalculatedBy'] = 2;
                $tax_Deduction['IsActive'] = true;
                $tax_Deduction['IsDelete'] = false;
                $tax_Deduction['IsTaxable'] = false;
                $tax_Deduction['DateCreated'] = date('Y-m-d h:i:s');
                SalaryAllowanceDeduction::query()->create($tax_Deduction);


            }
            DB::commit();


            return resp(1,'Single Payroll added Successfully!', [],Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPayrollDetail($isBonusSalary,$employeeId,$year,$month)
    {
        $payrollDetail = EmployeePayrollDetail::where('IsBonusSalary', $isBonusSalary)
            ->where('EmployeeId', $employeeId)
            ->where('IsDelete', false)
            ->where(function ($query) use ($year, $month) {
                $query->whereYear('PayMonth', $year)
                    ->whereMonth('PayMonth', $month)
                    ->orWhere(function ($query) use ($year, $month) {
                        $query->whereYear('PayPeriodFrom', $year)
                            ->whereMonth('PayPeriodFrom', $month);
                    });
            })
            ->first();
        return $payrollDetail;
    }
}
