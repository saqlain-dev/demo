<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Admin\ItemVariant;
use App\Models\EmployeePayrollDetail;
use App\Models\EmployeePayrollMaster;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceDetail;
use App\Models\Finance\FinanceBill\FinanceBill;
use App\Models\Finance\FinanceBill\FinanceBillDetail;
use App\Models\Finance\LasInvoice;
use App\Models\Finance\SalaryAccountConfiguration;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\JournalVoucherDetail;
use App\Models\Finance\Voucher\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use DateTime;
use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\Currency;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Invoice;
use App\Models\Reimbursement;
class ReportController extends Controller
{
    public function getTrialBalance(Request $request)
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'un_vouchers_view',
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        /*$request->validate([
            'account_no' => 'nullable|integer',
            'date_on' => 'required|date',
        ]);*/
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_no' => 'nullable|array',
            'nominal_class_id' => 'nullable|integer',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        try {
            DB::beginTransaction();
            $account_no = $request->account_no ?? NULL;
            $rawData = DB::select('EXEC GetTrialBalanceForAccount ?,?,?', [$account_no, $start_date, $end_date]);
            $trialbalance = json_decode(json_encode($rawData), true);

            $formattedData = $this->buildHierarchy($trialbalance);

            $data['trial_balance'] = $formattedData;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getIncomeExpanseDetailReport(Request $request)
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'un_vouchers_view',
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        /*$request->validate([
            'account_no' => 'nullable|integer',
            'date_on' => 'required|date',
        ]);*/
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_no' => 'nullable|array',
            'nominal_class_id' => 'nullable|integer',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        try {
            DB::beginTransaction();
            $nominal_class_id = $request->nominal_class_id ?? NULL;
            $account_no_array = $request->account_no ?? null;

            if (is_array($account_no_array)) {
                $account_no = implode(',', $account_no_array);
            } elseif (!empty($account_no_array)) {
                $account_no = (string) $account_no_array; // If it's a single value, cast it to string
            } else {
                $account_no = null;
            }

            $accountsRaw = DB::select('EXEC GetInconeExpenceWithAccountsOnly ?,?,?,?', [
                $account_no,
                $start_date,
                $end_date,
                $nominal_class_id
            ]);

            $transactionsRaw = DB::select('EXEC GetInconeExpenceWithActiveHierarchy ?,?,?,?', [
                $account_no,
                $start_date,
                $end_date,
                $nominal_class_id
            ]);



            // Convert objects to arrays
            $accounts = collect($accountsRaw)->map(fn($row) => (array) $row)->toArray();
            $transactions = collect($transactionsRaw)->map(fn($row) => (array) $row)->toArray();

            // Group transactions by account_id
            $txnsByAccount = [];
            foreach ($transactions as $txn) {
                $txnsByAccount[$txn['account_id']][] = $txn;
            }

            // Attach transactions to corresponding accounts
            foreach ($accounts as &$account) {
                $account['transactions'] = $txnsByAccount[$account['account_id']] ?? [];
            }

            // Now build the hierarchical structure
            $formattedData = $this->buildHierarchy($accounts);  // pass null or 0 based on your root parent id

            $data['trial_balance'] = $formattedData;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function getIncomeExpense(Request $request)
    {
        $request->validate([
            'date_on' => 'required|date',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($request->date_on));
        $start_date = date('Y-m-d', strtotime($financialYear->start_date));
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC GenerateIncomeExpenseReport ?,?', [$start_date, $end_date]);
            $income_expense = json_decode(json_encode($rawData), true);

            $formattedData = $this->buildHierarchy($income_expense);

            $data['income_expense_report'] = $formattedData;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function getIncomeTaxReport(Request $request)
    {

        $financialYear = FinancialYear::query()->where('id', $request->id)->first();
        $end_date = date('Y-m-d', strtotime($financialYear->end_date));
        $start_date = date('Y-m-d', strtotime($financialYear->start_date));
        $Description = 'TAX';
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC usp_GetEmployeeTaxAllowances ?,?,?', [$Description, $start_date, $end_date]);
            $income_tax = json_decode(json_encode($rawData), true);

            $data['income_tax_report'] = $income_tax;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getEmployeeDetailIncomeTaxReport(Request $request)
    {

        $payrollID = $request->payroll_id;
        $payroll = EmployeePayrollMaster::query()->where('id', $payrollID)->first();
        if ($payroll) {
            $end_date = date('Y-m-d', strtotime($payroll->PayPeriodTo));
            $start_date = date('Y-m-d', strtotime($payroll->PayPeriodFrom));
            $Description = 'TAX';
            try {
                DB::beginTransaction();
                $rawData = DB::select('EXEC usp_GetEmployeeTaxAllowances ?,?,?', [$Description, $start_date, $end_date]);
                $income_tax = json_decode(json_encode($rawData), true);

                $data['income_tax_report'] = $income_tax;
                DB::commit();
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
            }
        }
    }
    public function getVendorIncomeTaxReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $financialYear = FinancialYear::query()->where('status', 1)->first();
        //$end_date=date('Y-m-d',strtotime($financialYear->end_date));
        // $start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $Description = 'TAX';
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC usp_GetVendorIncomeTaxReport ?,?', [$start_date, $end_date]);
            $sale_tax = json_decode(json_encode($rawData), true);

            $data['sale_tax_report'] = $sale_tax;
            $data['payroll_vouchers'] = Voucher::query()->with('ledger.ledgerDetail', 'taxFilling')->whereBetween('Date', [$start_date, $end_date])->where('VoucherFrom', 'PAYROLL')->get();
            $data['salary_configuration'] = SalaryAccountConfiguration::query()->get();
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getBalanceSheet(Request $request)
    {
        $this->authorizeAny([
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);
        /*$request->validate([
            'date_on' => 'required|date',
        ]);*/
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $previousFinancialYear = $this->getPreviousFinancialYearDates($request->start_date);
        $data['previousFinancialYear'] = $previousFinancialYear;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        //return $data;
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC GenerateBalanceSheet ?,?', [$start_date, $end_date]);
            $balanceSheetData = json_decode(json_encode($rawData), true);
            $formattedData = $this->buildHierarchy($balanceSheetData);
            $currentYearBalanceSheet = $formattedData;
            $currentYearBalanceSheet['year'] = date('Y', strtotime($start_date));
            $data['balance_sheet'] = array_values($currentYearBalanceSheet);
            $rawData = DB::select('EXEC GenerateBalanceSheet ?,?', [$previousFinancialYear['start_date'], $previousFinancialYear['end_date']]);
            $balanceSheetData_previous = json_decode(json_encode($rawData), true);
            $formattedData_previous = $this->buildHierarchy($balanceSheetData_previous);
            $previousYearBalanceSheet = $formattedData_previous;
            $previousYearBalanceSheet['year'] = date('Y', strtotime($previousFinancialYear['start_date']));
            $data['balance_sheet_previous'] = array_values($previousYearBalanceSheet);
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function getSaleTaxReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $financialYear = FinancialYear::query()->where('status', 1)->first();
        //$end_date=date('Y-m-d',strtotime($financialYear->end_date));
        // $start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $Description = 'TAX';
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC usp_GetSaleTaxReport ?,?', [$start_date, $end_date]);
            $sale_tax = json_decode(json_encode($rawData), true);

            $data['sale_tax_report'] = $sale_tax;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function getCashFlow(Request $request)
    {
        $this->authorizeAny([
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $previousFinancialYear = $this->getPreviousFinancialYearDates($request->start_date);
        $data['previousFinancialYear'] = $previousFinancialYear;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        //return $data;
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC sp_CashFlowSurplus ?,?', [$start_date, $end_date]);
            $currentCashFlowData = json_decode(json_encode($rawData), true);
            $data['current_cash_flow_data'] = $currentCashFlowData;

            $rawData = DB::select('EXEC sp_CashFlowSurplus ?,?', [$previousFinancialYear['start_date'], $previousFinancialYear['end_date']]);
            $previousCashFlowData = json_decode(json_encode($rawData), true);

            $data['previous_cash_flow_data'] = $previousCashFlowData;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    function getPreviousFinancialYearDates($date)
    {
        // Financial year starts in April and ends in March
        $financialYearStartMonth = 7;
        $financialYearEndMonth = 6;

        // Convert the given date to a DateTime object
        $givenDate = new DateTime($date);

        // Get the year and month of the given date
        $year = (int) $givenDate->format('Y');
        $month = (int) $givenDate->format('m');

        // Determine the start and end dates of the previous financial year
        if ($month >= $financialYearStartMonth) {
            // If the given date is in or after April, the previous financial year started last year
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            // If the given date is before April, the previous financial year started two years ago
            $startYear = $year - 2;
            $endYear = $year - 1;
        }

        $startDate = new DateTime("$startYear-$financialYearStartMonth-01");
        $endDate = new DateTime("$endYear-$financialYearEndMonth-01");
        $endDate->modify('last day of this month');

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d')
        ];
    }
    public function getPayableReceivable(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $f_end_date = date('Y-m-d', strtotime($financialYear->end_date));
        $f_start_date = date('Y-m-d', strtotime($financialYear->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $account_codes = $request->account_codes ? implode(',', $request->account_codes) : NULL;

        if ($this->isInFinancialYear($start_date, $end_date, $f_start_date, $f_end_date)) {
            //return $data;
            try {
                DB::beginTransaction();
                $rawData = DB::select('EXEC GeneratePayableReceivable ?,?', [$start_date, $end_date]);
                //$payablesheet = json_decode(json_encode($rawData), true);


                //$data['payable_receivable'] = $payablesheet;

                $payablesheet = collect($rawData);

                $accounts = [];
                foreach ($payablesheet as $entry) {

                    $accounts[] = [
                        'ledger_id' => $entry->ledger_id,
                        'category' => $entry->category,
                        'transaction_date' => $entry->transaction_date,
                        'description' => $entry->transaction_description,
                        'debit_amount' => $entry->debit_amount,
                        'credit_amount' => $entry->credit_amount,
                        //'running_balance' => $entry->running_balance,
                        'NominalID' => $entry->NominalID,
                        'NominalClass' => $entry->NominalClass,
                        'NominalClassID' => $this->getNominalClass($entry->NominalClassID) ?? NULL,
                        'voucher_no' => $this->getVoucherNumber($entry->voucher_id) ?? NULL,
                        //'voucher_no' => $entry->voucher_no ?? NULL,
                        'account_id' => $entry->account_id ?? NULL,
                        'account_code' => $entry->account_code ?? NULL,
                        'account_name' => $entry->account_name ?? NULL,
                    ];
                }
                $accounts = array_values($accounts);
                $data['payable_receivable'] = $accounts;
                DB::commit();
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
            }
        } else {
            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function getPayableReceivableReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_code' => 'required|integer',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $f_end_date = date('Y-m-d', strtotime($financialYear->end_date));
        $f_start_date = date('Y-m-d', strtotime($financialYear->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $account_code = $request->account_code;

        //if ($this->isInFinancialYear($start_date, $end_date, $f_start_date, $f_end_date)) {
        //return $data;
        try {
            DB::beginTransaction();
            $rawData = DB::select('EXEC GeneratePayableReceivableReport ?,?,?', [$start_date, $end_date, $account_code]);


            $payablesheet = collect($rawData);

            $accounts = [];
            foreach ($payablesheet as $entry) {

                $accounts[] = [
                    'ledger_id' => $entry->ledger_id,
                    'category' => $entry->category,
                    'transaction_date' => $entry->transaction_date,
                    'description' => $entry->transaction_description,
                    'debit_amount' => $entry->debit_amount,
                    'credit_amount' => $entry->credit_amount,
                    //'running_balance' => $entry->running_balance,
                    'NominalID' => $entry->NominalID,
                    'NominalClass' => $entry->NominalClass,
                    'NominalClassID' => $this->getNominalClass($entry->NominalClassID) ?? NULL,
                    'voucher_no' => $this->getVoucherNumber($entry->voucher_id) ?? NULL,
                    //'voucher_no' => $entry->voucher_no ?? NULL,
                    'days_since_transaction' => $entry->days_since_transaction ?? NULL,
                    'account_id' => $entry->account_id ?? NULL,
                    'account_code' => $entry->account_code ?? NULL,
                    'account_name' => $entry->account_name ?? NULL,
                ];
            }
            $accounts = array_values($accounts);
            $data['payable_receivable'] = $accounts;
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
        /*}else{
            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }*/
    }

    function buildPayableReceivableHierarchy($entries, $parentId = 0)
    {
        $branch = [];

        foreach ($entries as $entry) {

            if ($entry['parent_account_id'] == $parentId) {

                $children = $this->buildPayableReceivableHierarchy($entries, $entry['account_id']);
                if ($children) {

                    $entry[$entry['account_code']] = $children;
                    // $entry[$entry['account_code'].' - '.$entry['account_name']] = $children;
                    // $element['children'] = $children;
                }
                $branch[] = $entry;
            }
        }

        return $branch;
    }

    //    function getPayableReport()
//    {
//        // Calculate total payable from the finance_bill table
//        $totalPayable = FinanceBill::sum('total');
//
//        // Calculate breakdown by each chart_of_account from the finance_bill_details table, including date and description
//        $breakdown = FinanceBillDetail::select(
//            'finance_bill_details.item_coa',
//            'finance_bills.date',
//            'finance_bill_details.description as detail_description', // Description from finance_bill_details
//            'finance_bills.description as bill_description', // Description from finance_bills
//            DB::raw('SUM(finance_bill_details.total) as total_amount')
//        )
//            ->join('finance_bills', 'finance_bill_details.bill_id', '=', 'finance_bills.id') // Join with finance_bills
//            ->groupBy('finance_bill_details.item_coa', 'finance_bills.date', 'finance_bill_details.description', 'finance_bills.description') // Group by item_coa, date, and descriptions
//            ->with('chartOfAccount') // Optional, if you want the full chart_of_account details
//            ->get();
//
//        return [
//            'total_payable' => $totalPayable,
//            'breakdown' => $breakdown,
//        ];
//    }

    function getPayableReport(Request $request)
    {

        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);
        // Ensure dates are provided and valid
        $startDate = $this->input['start_date'] ?: now()->format('Y-m-d'); // Default start date if not provided
        $endDate = $this->input['end_date'] ?: now()->format('Y-m-d'); // Default end date as today if not provided

        // Calculate total payable from the finance_bill table within the date range
        $totalPayable = FinanceBill::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('total')
            ->sum('total');

        // Calculate breakdown by each chart_of_account from the finance_bill_details table, including date and description
        $breakdown = FinanceBillDetail::select(
            'finance_bill_details.item_coa',
            'finance_bills.date',
            'finance_bill_details.description as detail_description', // Description from finance_bill_details
            'finance_bills.description as bill_description', // Description from finance_bills
            DB::raw('SUM(finance_bill_details.total) as total_amount')
        )
            ->join('finance_bills', 'finance_bill_details.bill_id', '=', 'finance_bills.id') // Inner join to ensure matching records
            ->whereBetween('finance_bills.date', [$startDate, $endDate]) // Date filter
            ->whereNotNull('finance_bill_details.item_coa') // Filter out null item_coa
            ->whereNotNull('finance_bill_details.total') // Filter out null total values
            ->where('finance_bill_details.total', '>', 0) // Ensure total is greater than 0 if applicable
            ->groupBy('finance_bill_details.item_coa', 'finance_bills.date', 'finance_bill_details.description', 'finance_bills.description')
            ->with('chartOfAccount') // Optional: Eager load chart of account details
            ->get();

        return [
            'total_payable' => $totalPayable,
            'breakdown' => $breakdown,
        ];
    }


    function getReceivableReport(Request $request)
    {
        // Set default date range if not provided
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $startDate = $this->input['start_date'] ?: now()->format('Y-m-d'); // Default start date if not provided
        $endDate = $this->input['end_date'] ?: now()->format('Y-m-d'); // Default end date as today if not provided

        // Calculate total receivables from the customer_invoices table within the date range
        $totalReceivables = CustomerInvoice::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('total_amount')
            ->sum('total_amount');

        // Calculate breakdown by each chart_of_account from the customer_invoice_details table, including date and description
        $breakdown = CustomerInvoiceDetail::select(
            'customer_invoice_details.item_coa',
            'customer_invoices.date',
            'customer_invoice_details.description as detail_description', // Description from customer_invoice_details
            'customer_invoices.narration as invoice_narration', // Narration from customer_invoices
            DB::raw('SUM(customer_invoice_details.total) as total_amount')
        )
            ->join('customer_invoices', 'customer_invoice_details.customer_invoice_id', '=', 'customer_invoices.id') // Inner join to ensure matching records
            ->whereBetween('customer_invoices.date', [$startDate, $endDate]) // Date filter
            ->whereNotNull('customer_invoice_details.item_coa') // Filter out null item_coa
            ->whereNotNull('customer_invoice_details.total') // Filter out null total values
            ->where('customer_invoice_details.total', '>', 0) // Ensure total is greater than 0 if applicable
            ->groupBy('customer_invoice_details.item_coa', 'customer_invoices.date', 'customer_invoice_details.description', 'customer_invoices.narration')
            ->with('chartOfAccount') // Optional: Eager load chart of account details if needed
            ->get();

        return [
            'total_receivables' => $totalReceivables,
            'breakdown' => $breakdown,
        ];
    }

    function getFinancePayableReceiveableReport(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        // Extract start and end dates from the request
        $startDate = $request->input('start_date') ?: now()->format('Y-m-d');
        $endDate = $request->input('end_date') ?: now()->format('Y-m-d');

        // Calculate total payable from the finance_bill table within the date range
        $totalPayable = FinanceBill::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('total')
            ->sum('total');

        // Calculate breakdown by each chart_of_account from the finance_bill_details table
        $payableBreakdown = FinanceBillDetail::select(
            'finance_bill_details.item_coa',
            'finance_bills.date',
            'finance_bill_details.description as detail_description',
            'finance_bills.description as bill_description',
            DB::raw('SUM(finance_bill_details.total) as total_amount')
        )
            ->join('finance_bills', 'finance_bill_details.bill_id', '=', 'finance_bills.id')
            ->whereBetween('finance_bills.date', [$startDate, $endDate])
            ->whereNotNull('finance_bill_details.item_coa')
            ->whereNotNull('finance_bill_details.total')
            ->where('finance_bill_details.total', '>', 0)
            ->groupBy('finance_bill_details.item_coa', 'finance_bills.date', 'finance_bill_details.description', 'finance_bills.description')
            ->with('chartOfAccount') // Optional: Eager load chart of account details if needed
            ->get();

        // Calculate total receivables from the customer_invoices table within the date range
        $totalReceivables = CustomerInvoice::whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('total_amount')
            ->sum('total_amount');

        // Calculate breakdown by each chart_of_account from the customer_invoice_details table
        $receivableBreakdown = CustomerInvoiceDetail::select(
            'customer_invoice_details.item_coa',
            'customer_invoices.date',
            'customer_invoice_details.description as detail_description',
            'customer_invoices.narration as invoice_narration',
            DB::raw('SUM(customer_invoice_details.total) as total_amount')
        )
            ->join('customer_invoices', 'customer_invoice_details.customer_invoice_id', '=', 'customer_invoices.id')
            ->whereBetween('customer_invoices.date', [$startDate, $endDate])
            ->whereNotNull('customer_invoice_details.item_coa')
            ->whereNotNull('customer_invoice_details.total')
            ->where('customer_invoice_details.total', '>', 0)
            ->groupBy('customer_invoice_details.item_coa', 'customer_invoices.date', 'customer_invoice_details.description', 'customer_invoices.narration')
            ->with('chartOfAccount') // Optional: Eager load chart of account details if needed
            ->get();

        // Return both payable and receivable reports in a single response
        return response()->json([
            'payables' => [
                'total_payable' => $totalPayable,
                'breakdown' => $payableBreakdown,
            ],
            'receivables' => [
                'total_receivables' => $totalReceivables,
                'breakdown' => $receivableBreakdown,
            ],
        ]);
    }

    function getPayableAndReceivableReport(Request $request)
    {
        $this->authorizeAny([
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        // Set date range if provided
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Define payable and receivable voucher types
        $payableVoucherTypes = ['BPV', 'CPV', 'CDR', 'JV']; // Bank Payment Voucher, Cash Payment Voucher
        $receivableVoucherTypes = ['BRV', 'CRV']; // Bank Receipt Voucher, Cash Receipt Voucher

        // Calculate total payable
        $totalPayable = JournalVoucher::whereIn('voucher_type', $payableVoucherTypes)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // Get payable breakdown from journal_voucher_details
        $payableBreakdown = JournalVoucherDetail::select(
            'journal_voucher_details.nominal_id as nominal_code',  // Use nominal_code for clarity
            'chart_of_accounts.name as account_name',
            'journal_vouchers.date',
            'journal_voucher_details.detail',
            DB::raw('SUM(journal_voucher_details.credit) as total_payable')
        )
            ->join('journal_vouchers', 'journal_voucher_details.journal_voucher_id', '=', 'journal_vouchers.id')
            ->join('chart_of_accounts', 'journal_voucher_details.nominal_id', '=', 'chart_of_accounts.code')  // Join on code instead of id
            ->whereIn('journal_vouchers.voucher_type', $payableVoucherTypes)
            ->whereBetween('journal_vouchers.date', [$startDate, $endDate])
            ->groupBy('journal_voucher_details.nominal_id', 'chart_of_accounts.name', 'journal_vouchers.date', 'journal_voucher_details.detail')
            // Assuming nominal_id maps to the Chart of Accounts
            ->get();


        // Calculate total receivable
        $totalReceivable = JournalVoucher::whereIn('voucher_type', $receivableVoucherTypes)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        // Get receivable breakdown from journal_voucher_details
        $receivableBreakdown = JournalVoucherDetail::select(
            'journal_voucher_details.nominal_id as nominal_code',  // Use nominal_code for clarity
            'chart_of_accounts.name as account_name',
            'journal_vouchers.date',
            'journal_voucher_details.detail',
            DB::raw('SUM(journal_voucher_details.debit) as total_receivable')
        )
            ->join('journal_vouchers', 'journal_voucher_details.journal_voucher_id', '=', 'journal_vouchers.id')
            ->join('chart_of_accounts', 'journal_voucher_details.nominal_id', '=', 'chart_of_accounts.code')  // Join on code instead of id
            ->whereIn('journal_vouchers.voucher_type', $receivableVoucherTypes)
            ->whereBetween('journal_vouchers.date', [$startDate, $endDate])
            ->groupBy('journal_voucher_details.nominal_id', 'chart_of_accounts.name', 'journal_vouchers.date', 'journal_voucher_details.detail')
            // Assuming nominal_id maps to the Chart of Accounts
            ->get();

        return [
            'total_payable' => $totalPayable,
            'payable_breakdown' => $payableBreakdown,
            'total_receivable' => $totalReceivable,
            'receivable_breakdown' => $receivableBreakdown,
        ];
    }

    function getBudgetVarianceReport(Request $request)
    {
        $request->validate([
            'project_id' => 'required|integer',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        $projectId = $request->input('project_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch Budget Data from project_budgets and project_budget_details
        $projectBudgets = ProjectBudgetDetail::select(
            'category_id',
            DB::raw('SUM(requested_funds) as total_budget')
        )
            ->join('project_budgets', 'project_budget_details.project_budget_id', '=', 'project_budgets.id')
            ->where('project_budgets.project_id', $projectId)
            ->whereBetween('project_budgets.from_date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Fetch Actual Payables and Receivables (from the function getPayableAndReceivableReport)
        $payablesAndReceivables = getPayableAndReceivableReport($request);

        // Fetch Actual Payables based on head_id
        $payableBreakdown = $payablesAndReceivables['payable_breakdown'];
        $actualPayables = [];
        foreach ($payableBreakdown as $payable) {
            $headId = $payable->nominal_id;
            $actualPayables[$headId] = $payable->total_payable;
        }

        // Fetch Actual Receivables based on head_id
        $receivableBreakdown = $payablesAndReceivables['receivable_breakdown'];
        $actualReceivables = [];
        foreach ($receivableBreakdown as $receivable) {
            $headId = $receivable->nominal_id;
            $actualReceivables[$headId] = $receivable->total_receivable;
        }

        // Calculate Variance
        $varianceReport = [];
        foreach ($projectBudgets as $categoryId => $budget) {
            $actualPayable = $actualPayables[$categoryId] ?? 0;
            $actualReceivable = $actualReceivables[$categoryId] ?? 0;

            // Calculate Variance
            $totalActual = $actualPayable - $actualReceivable;
            $variance = $budget->total_budget - $totalActual;

            $varianceReport[] = [
                'category_id' => $categoryId,
                'budgeted_amount' => $budget->total_budget,
                'actual_payable' => $actualPayable,
                'actual_receivable' => $actualReceivable,
                'variance' => $variance,
            ];
        }

        return [
            'variance_report' => $varianceReport,
        ];
    }



    function BudgetVarianceReport(Request $request)
    {
        // Validate input dates
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'project_id' => 'required',
        ]);

        // Set start and end dates
        $FYID = FinancialYear::query()->where('status', 1)->with('financialYear')->first();
        /*$startDate = $FYID->start_date;
        $endDate = $FYID->end_date;*/

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $projectId = $request->input('project_id');


        $varianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoin('chart_of_accounts as coa', 'bd.head_id', '=', 'coa.id')
            ->leftJoinSub(
                DB::table('tbl_general_ledgers as tgl')
                    ->select(
                        'tgl.project_id',
                        'tgld.NominalID as NominalID',
                        'coai.id as chart_of_account_id',
                        'tgld.NominalClassID as head_class_id',
                        DB::raw('SUM(tgld.debit) - SUM(tgld.credit) as expense_amount')
                    )
                    ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
                    ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
                    ->whereBetween('tgl.date', [$startDate, $endDate])
                    ->groupBy('tgl.project_id', 'tgld.NominalID', 'tgld.NominalClassID', 'coai.id'),
                'v',
                function ($join) {
                    $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                        ->on('hc.id', '=', 'v.head_class_id');
                }
            )
            ->where('p.id', $projectId)
            ->groupBy('p.id', 'p.project_name', 'hc.name')
            ->get();

        return response()->json([
            'variance_report' => $varianceReport,
        ]);
    }

    public function projectBudget($budgetID)
    {
        $projectBudget = ProjectBudget::query()->where('id', $budgetID)->first();

        $projectBudget = $projectBudget->load([
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

        ]);
        return $projectBudget;

    }
    function BudgetVarianceItemWiseReport(Request $request)
    {
        // Validate input dates
        $request->validate([
            'project_id' => 'required',
            'budget_id' => 'required',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
        ]);

        // Set start and end dates
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $projectId = $request->input('project_id');

        // Fetch project budget structure (unchanged)
        $projectBudget = $this->projectBudget($request->input('budget_id'));

        /*
         * 🔁 IMPORTANT CHANGE:
         * Expenses are NO LONGER matched using tgl.project_id
         * because ledger project_id data is inconsistent.
         *
         * Now matching logic:
         * tgld.NominalClassID (ledger details)
         *          =
         * hc.id (head_classes)
         *
         * This ensures correct variance calculation without changing
         * output structure.
         */

        // Subquery: Expense grouped by NominalClassID + Account
        $expenseSubQuery = DB::table('tbl_general_ledgers as tgl')
            ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
            ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
            ->whereBetween('tgl.Date', [$startDate, $endDate])
            ->select(
                'tgld.NominalClassID as head_class_id', // 🔁 KEY MATCH FIELD
                'coai.id as chart_of_account_id',
                DB::raw('SUM(COALESCE(tgld.Debit,0) - COALESCE(tgld.Credit,0)) as expense_amount')
            )
            ->groupBy('tgld.NominalClassID', 'coai.id');
        // get all time expense
        $allTimeExpenseSubQuery = DB::table('tbl_general_ledgers as tgl')
            ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
            ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
            ->select(
                'tgld.NominalClassID as head_class_id',
                'coai.id as chart_of_account_id',
                DB::raw('SUM(COALESCE(tgld.Debit,0) - COALESCE(tgld.Credit,0)) as all_time_expense')
            )
            ->groupBy('tgld.NominalClassID', 'coai.id');
        // Current FY Variance (FIXED MATCHING)
        $CurrentFYVarianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                'bd.head_id as budget_head_id',
                'bd.id as BudgetDetailId',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(at.all_time_expense), 0) as all_time_expense'),
                DB::raw('COALESCE(SUM(at.all_time_expense), 0) - COALESCE(SUM(v.expense_amount), 0) as previous_expense'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoinSub($expenseSubQuery, 'v', function ($join) {
                $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                    ->on('hc.id', '=', 'v.head_class_id'); // 🔁 MATCH USING NominalClassID
            })
            ->leftJoinSub($allTimeExpenseSubQuery, 'at', function ($join) {
                $join->on('bd.head_id', '=', 'at.chart_of_account_id')
                    ->on('hc.id', '=', 'at.head_class_id');
            })
            ->where('p.id', $projectId)
            ->groupBy(
                'p.id',
                'p.project_name',
                'hc.name',
                'bd.head_id',
                'bd.id'
            )
            ->get();

        // Overall Variance (Same logic, same structure)
        $OverAllVarianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                'bd.head_id as budget_head_id',
                'bd.id as BudgetDetailId',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoinSub($expenseSubQuery, 'v', function ($join) {
                $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                    ->on('hc.id', '=', 'v.head_class_id'); // 🔁 SAME FIX HERE
            })
            ->where('p.id', $projectId)
            ->groupBy(
                'p.id',
                'p.project_name',
                'hc.name',
                'bd.head_id',
                'bd.id'
            )
            ->get();

        // Attach grand total expenses (UNCHANGED OUTPUT STRUCTURE)
        // if ($CurrentFYVarianceReport) {
        //     foreach ($CurrentFYVarianceReport as $key => $current) {
        //         $CurrentFYVarianceReport[$key]->grand_total_expenses =
        //             $OverAllVarianceReport[$key]->all_time_expense ?? 0;

        //     }
        // }

        // Map variance report into budget details (UNCHANGED STRUCTURE)
        if ($projectBudget && isset($projectBudget->BudgetDetail)) {
            foreach ($projectBudget->BudgetDetail as $key => $budget) {
                if (isset($CurrentFYVarianceReport[$key])) {
                    $projectBudget->BudgetDetail[$key]->currentFYVarianceReport =
                        $CurrentFYVarianceReport[$key];
                } else {
                    $projectBudget->BudgetDetail[$key]->currentFYVarianceReport = null;
                }
            }
        }

        $currencies = Currency::where('is_active', 1)
            ->with('currencyDetails')
            ->get();

        return response()->json([
            'variance_report' => $projectBudget,
            'currencies' => $currencies,
        ]);
    }


    public function getGeneralJournalReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_codes' => 'nullable|array',
            'account_codes.*' => 'nullable',
        ]);
        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $f_end_date = date('Y-m-d', strtotime($financialYear->end_date));
        $f_start_date = date('Y-m-d', strtotime($financialYear->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $account_codes = $request->account_codes ? implode(',', $request->account_codes) : NULL;
        //return $data;
        if ($this->isInFinancialYear($start_date, $end_date, $f_start_date, $f_end_date)) {
            try {
                DB::beginTransaction();
                $data['general_journal_bpv'] = DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date, $end_date, $account_codes, 'BPV']);
                $data['general_journal_cpv'] = DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date, $end_date, $account_codes, 'CPV']);
                $data['general_journal_brv'] = DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date, $end_date, $account_codes, 'BRV']);
                $data['general_journal_crv'] = DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date, $end_date, $account_codes, 'CRV']);
                $data['general_journal_jv'] = DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date, $end_date, $account_codes, 'JV']);

                DB::commit();
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
            }
        } else {

            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }

    }
    function isInFinancialYear($startDate, $endDate, $financialYearStart, $financialYearEnd)
    {
        // Convert dates to DateTime objects for easy comparison
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        $financialYearStart = new DateTime($financialYearStart);
        $financialYearEnd = new DateTime($financialYearEnd);

        // Check if start date is after or equal to financial year start
        // and end date is before or equal to financial year end
        return ($startDate >= $financialYearStart && $endDate <= $financialYearEnd);
    }

    public function getGeneralLedger(Request $request)
    {
        $this->authorizeAny([
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_codes' => 'nullable|array'
        ]);

        $account_codes = $request->account_codes ? array_filter($request->account_codes) : [];
        $account = $request->account_codes ? array_filter($request->account_codes) : [];
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $account_codes = !empty($account_codes) ? implode(',', $account_codes) : NULL;
        $account_codes_array = !empty($account) ? $account : [];


        //return $data;
        try {
            DB::beginTransaction();
            $result = DB::select('EXEC GenerateGeneralLedgerReport ?,?,?', [$start_date, $end_date, $account_codes]);
            $ledgerEntries = collect($result);

            $accounts = [];
            foreach ($ledgerEntries as $entry) {

                $accounts[$entry->account_code]['account_id'] = $entry->account_id;
                $accounts[$entry->account_code]['account_code'] = $entry->account_code;
                $accounts[$entry->account_code]['parent_account_id'] = $entry->parent_id;
                $accounts[$entry->account_code]['account_name'] = $entry->account_name;
                $accounts[$entry->account_code]['transactions'][] = [
                    'ledger_id' => $entry->ledger_id,
                    'transaction_date' => $entry->transaction_date,
                    'description' => $entry->transaction_description,
                    'debit_amount' => $entry->debit_amount,
                    'credit_amount' => $entry->credit_amount,
                    'running_balance' => $entry->running_balance,
                    'NominalID' => $entry->NominalID,
                    'NominalClass' => $entry->NominalClass,
                    'NominalClassID' => $entry->NominalClassID,
                ];
            }
            $accounts = array_values($accounts); // Reset array keys
            $ledgerTransaction = [];
            if ($account_codes_array) {
                foreach ($account_codes_array as $key => $code) {
                    $AccountDetail = ChartOfAccount::query()->where('code', $code)->first();
                    $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts, $AccountDetail['parent_id']);
                }
            } else {
                $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts);
            }


            $data['general_ledger'] = $ledgerTransaction;
            $data['general_ledger'] = $data['general_ledger'][0];
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function getGeneralLedgerDetail(Request $request)
    {
        $this->authorizeAny([
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            'account_codes' => 'nullable|array',
            'nominal_class_id' => 'nullable|integer',
        ]);

        $account_codes = $request->account_codes ? array_filter($request->account_codes) : [];
        $account = $request->account_codes ? array_filter($request->account_codes) : [];
        $nominal_class_id = $request->nominal_class_id;
        $start_date = date('Y-m-d', strtotime($request->start_date));
        $end_date = date('Y-m-d', strtotime($request->end_date));
        $account_codes = !empty($account_codes) ? implode(',', $account_codes) : NULL;
        $nominal_class_id = !empty($nominal_class_id) ? $nominal_class_id : NULL;
        $account_codes_array = !empty($account) ? $account : [];


        //return $data;
        try {
            DB::beginTransaction();
            $result = DB::select('EXEC GenerateGeneralLedgerDetailReport ?,?,?,?', [$start_date, $end_date, $account_codes, $nominal_class_id]);
            $ledgerEntries = collect($result);

            $accounts = [];
            foreach ($ledgerEntries as $entry) {

                $accounts[] = [
                    'ledger_id' => $entry->ledger_id,
                    'transaction_date' => $entry->transaction_date,
                    'description' => $entry->transaction_description,
                    'debit_amount' => $entry->debit_amount,
                    'credit_amount' => $entry->credit_amount,
                    //'running_balance' => $entry->running_balance,
                    'NominalID' => $entry->NominalID,
                    'NominalClass' => $entry->NominalClass,
                    'NominalClassID' => $this->getNominalClass($entry->NominalClassID) ?? NULL,
                    'voucher_no' => $this->getVoucherNumber($entry->voucher_no, $entry->VoucherType) ?? NULL,
                    //'voucher_no' => $entry->voucher_no ?? NULL,
                    'account_id' => $entry->account_id ?? NULL,
                    'account_code' => $entry->account_code ?? NULL,
                    'account_name' => $entry->account_name ?? NULL,
                ];
            }
            $accounts = array_values($accounts); // Reset array keys
            /* $ledgerTransaction= [];
             if ($account_codes_array){
                 foreach ($account_codes_array as $key => $code){
                     $AccountDetail = ChartOfAccount::query()->where('code',$code)->first();
                     $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts, $AccountDetail['parent_id']);
                 }
             }else{
                 $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts);
             }*/

            $ledgerTransaction = $accounts;

            $data['general_ledger'] = $ledgerTransaction;
            //$data['general_ledger'] = $data['general_ledger'][0];
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    function getVoucherNumber($voucherID, $VoucherType = null)
    {
        $query = Voucher::query()->where('id', $voucherID);

        if ($VoucherType !== null) {
            $query->where('VoucherType', $VoucherType);
        }

        return $query->first(); // returns null if not found
    }


    function getNominalClass($NominalClassID)
    {
        $NominalClass = HeadClass::query()->where('id', $NominalClassID)->first();
        if ($NominalClass) {
            return $NominalClass->name;
        } else {
            return NULL;
        }
    }
    function buildGeneralLedgerHierarchy($entries, $parentId = 0)
    {
        $branch = [];

        foreach ($entries as $entry) {

            if ($entry['parent_account_id'] == $parentId) {

                $children = $this->buildGeneralLedgerHierarchy($entries, $entry['account_id']);
                if ($children) {

                    $entry[$entry['account_code']] = $children;
                    // $entry[$entry['account_code'].' - '.$entry['account_name']] = $children;
                    // $element['children'] = $children;
                }
                $branch[] = $entry;
            }
        }

        return $branch;
    }

    function mapAccountCodeToId($entries, $accountCode)
    {
        foreach ($entries as $entry) {
            if ($entry['account_code'] == $accountCode) {
                return $entry['account_id'];
            }
        }
        return null; // Handle case where account code is not found
    }

    function findAndBuildHierarchyByCode($entries, $startAccountCodes = [])
    {
        if (empty($startAccountCodes)) {
            // If no startAccountCodes provided, build the hierarchy from the top level (parent_account_id = 0)
            return $this->buildGeneralLedgerHierarchy($entries);
        }

        // Map account codes to account IDs
        $startAccountIds = [];
        foreach ($startAccountCodes as $code) {
            $accountId = $this->mapAccountCodeToId($entries, $code);
            if ($accountId !== null) {
                $startAccountIds[] = $accountId;
            }
        }

        $hierarchies = [];

        foreach ($startAccountIds as $startAccountId) {
            // Build the hierarchy starting from each selected account's ID
            $hierarchy = $this->buildGeneralLedgerHierarchy($entries, $startAccountId);

            // Add to hierarchies array
            $hierarchies[] = $hierarchy;
        }

        // Merge hierarchies or process as needed (e.g., combine into a single hierarchy)
        // Example: For now, returning array of hierarchies
        return $hierarchies;
    }
    private function buildHierarchy(array $elements, $parentId = 0)
    {

        $branch = [];

        foreach ($elements as $element) {

            if ($element['parent_account_id'] == $parentId) {

                $children = $this->buildHierarchy($elements, $element['account_id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
            }
        }

        return array_values($branch);
    }

    function BudgetVarianceAllProjectReport()
    {

        // Set start and end dates
        $FYID = FinancialYear::query()->where('status', 1)->with('financialYear')->first();
        $startDate = $FYID->start_date;
        $endDate = $FYID->end_date;
        // $projectId = $request->input('project_id');


        $varianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                'b.id as BudgetID',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoin('chart_of_accounts as coa', 'bd.head_id', '=', 'coa.id')
            ->leftJoinSub(
                DB::table('tbl_general_ledgers as tgl')
                    ->select(
                        'tgl.project_id',
                        'tgld.NominalID as NominalID',
                        'coai.id as chart_of_account_id',
                        'tgld.NominalClassID as head_class_id',
                        DB::raw('SUM(tgld.debit) - SUM(tgld.credit) as expense_amount')
                    )
                    ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
                    ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
                    ->whereBetween('tgl.date', [$startDate, $endDate])
                    ->groupBy('tgl.project_id', 'tgld.NominalID', 'tgld.NominalClassID', 'coai.id'),
                'v',
                function ($join) {
                    $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                        ->on('hc.id', '=', 'v.head_class_id');
                }
            )
            ->whereNull('b.deleted_at')
            ->where('p.approval_status', 1)
            ->groupBy('p.id', 'p.project_name', 'hc.name', 'b.id')
            ->get()->unique(function ($item) {
                return $item->BudgetID; // Ensure unique BudgetID & HeadClass
            })
            ->values();

        return $varianceReport;
    }
    public function itemVariantReport(Request $request)
    {
        $data['itemVariantList'] = ItemVariant::query()->with('item', 'assignToEmploy', 'assignToDept')->whereNotNull('assign_to_emp')->orWhereNotNull('assign_to_dept')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    function financeDashboard()
    {
        $this->authorizeAny([
            'manage_payment_payable',
            'manage_payment_receivable',
            'manage_financial_reporting',
            'manage_audit_financial_reporting',
        ]);

        $budget_expence = $this->BudgetVarianceAllProjectReport();
        $totalBudget = collect($budget_expence)->sum('total_budget');
        $totalExpenses = collect($budget_expence)->sum('total_expenses');
        $distinctProjects = collect($budget_expence)->unique('project_id')->count();

        $data['total_budget'] = $totalBudget;
        $data['total_expenses'] = $totalExpenses;
        $data['total_projects'] = $distinctProjects;
        $data['budget_expense_project_wise'] = $budget_expence;
        $data['project_budgets'] = ProjectBudget::with(['ProjectId', 'created_by', 'updated_by', 'BudgetDetail.Head' => ['parent', 'AccountTypeId']])->get();

        // Payable Receivable

        $financialYear = FinancialYear::query()->where('status', 1)->first();
        $end_date = date('Y-m-d', strtotime($financialYear->end_date));
        $start_date = date('Y-m-d', strtotime($financialYear->start_date));
        $rawData = DB::select('EXEC GeneratePayableReceivable ?,?', [$start_date, $end_date]);

        $payablesheet = collect($rawData);

        $accounts = [];
        foreach ($payablesheet as $entry) {

            $accounts[] = [
                'ledger_id' => $entry->ledger_id,
                'category' => $entry->category,
                'transaction_date' => $entry->transaction_date,
                'description' => $entry->transaction_description,
                'debit_amount' => $entry->debit_amount,
                'credit_amount' => $entry->credit_amount,
                //'running_balance' => $entry->running_balance,
                'NominalID' => $entry->NominalID,
                'NominalClass' => $entry->NominalClass,
                //'voucher_no' => $entry->voucher_no ?? NULL,
                'account_id' => $entry->account_id ?? NULL,
                'account_code' => $entry->account_code ?? NULL,
                'account_name' => $entry->account_name ?? NULL,
            ];
        }
        $accounts = array_values($accounts);
        $payable_receivable = $accounts;

        $payableReceivable = $payable_receivable; // Assuming this is your data

        $totalPayable = collect($payableReceivable)->sum('credit_amount');
        $totalReceivable = collect($payableReceivable)->sum('debit_amount');

        $data['total_payable'] = $totalPayable;
        $data['total_receivable'] = $totalReceivable;

        // Monthly Payable and Receivable
        $payableReceivable = collect($payableReceivable); // Assuming this is your data

        $monthlySummary = $payableReceivable->groupBy('transaction_date')
            ->map(function ($items, $key) {
                return [
                    'date' => date('M-y', strtotime($key)),
                    'total_payable' => $items->sum('credit_amount'),
                    'total_receivable' => $items->sum('debit_amount'),
                ];
            })->values();

        $data['monthly_summary'] = $monthlySummary;



        //Aggregating data for pie chart
        $invoiceCount = Invoice::query()
            ->where('is_voucher_posted', 0)
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->whereNull('deleted_at')
                    ->whereNotNull('VoucherFromID')
                    ->whereIn('VoucherFrom', ['Invoice']);
            })
            ->count();

        $billCount = AdminInvoice::query()
            ->where(['approval_status' => 1, 'is_estimate' => 0])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'Admin Bill');
            })
            ->count();

        $reimbursementCount = Reimbursement::query()
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'Reimbursements')
                    ->whereNotNull('VoucherFromID');
            })
            ->count();

        $travelExpenseCount = ClaimTravelExpense::query()
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'Travel Expense');
            })
            ->count();

        $courtExpenseCount = CourtExpense::query()
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'Court Expense');
            })
            ->count();

        $advanceSalaryCount = AdvanceSalary::query()
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'LOAN');
            })
            ->count();

        $las_invoices = LasInvoice::query()
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function ($query) {
                $query->select('VoucherFromID')
                    ->from('vouchers')
                    ->where('VoucherFrom', 'LAS-INV');
            })
            ->count();

        // Preparing data for the pie chart
        $data['payment_cart'] = [
            'invoice' => $invoiceCount,
            // 'Partner Invoice' => $partnerInvoiceCount,
            'bills_and_utilities' => $billCount,
            'reimbursements' => $reimbursementCount,
            'travel_expenses' => $travelExpenseCount,
            'court_expenses' => $courtExpenseCount,
            'Advance_salary' => $advanceSalaryCount,
            'las_invoice' => $las_invoices,
            'total' => $invoiceCount + $billCount + $reimbursementCount + $travelExpenseCount + $courtExpenseCount + $advanceSalaryCount + $las_invoices,
        ];

        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);

    }



}
