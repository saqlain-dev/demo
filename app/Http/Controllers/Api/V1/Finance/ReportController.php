<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Admin\ItemVariant;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceDetail;
use App\Models\Finance\FinanceBill\FinanceBill;
use App\Models\Finance\FinanceBill\FinanceBillDetail;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\JournalVoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use DateTime;

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

        $request->validate([
            'account_no' => 'nullable|integer',
            'date_on' => 'required|date',
        ]);
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $end_date=date('Y-m-d',strtotime($request->date_on));
        $start_date=date('Y-m-d',strtotime($financialYear->start_date));
        try {
            DB::beginTransaction();
            $account_no = $request->account_no ?? NULL;
            $rawData= DB::select('EXEC GetTrialBalanceForAccount ?,?,?', [$account_no,$start_date,$end_date]);
            $trialbalance = json_decode(json_encode($rawData), true);

            $formattedData = $this->buildHierarchy($trialbalance);

            $data['trial_balance']=$formattedData;
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
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $end_date=date('Y-m-d',strtotime($request->date_on));
        $start_date=date('Y-m-d',strtotime($financialYear->start_date));
        try {
            DB::beginTransaction();
            $rawData= DB::select('EXEC GenerateIncomeExpenseReport ?,?', [$start_date,$end_date]);
            $income_expense = json_decode(json_encode($rawData), true);

            $formattedData = $this->buildHierarchy($income_expense);

            $data['income_expense_report']=$formattedData;
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

        $request->validate([
            'date_on' => 'required|date',
        ]);

        $financialYear=FinancialYear::query()->where('status',1)->first();
        $end_date=date('Y-m-d',strtotime($financialYear->end_date));
        $start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $previousFinancialYear = $this->getPreviousFinancialYearDates($financialYear->start_date);
        $data['start_date']=$start_date;
        $data['end_date']=$end_date;
        //return $data;
        try {
            DB::beginTransaction();
            $rawData= DB::select('EXEC GenerateBalanceSheet ?,?', [$start_date,$end_date]);
            $balanceSheetData = json_decode(json_encode($rawData), true);

            $formattedData = $this->buildHierarchy($balanceSheetData);

            $currentYearBalanceSheet=$formattedData;
            $currentYearBalanceSheet['year']=date('Y',strtotime($start_date));
            $data['balance_sheet']= array_values($currentYearBalanceSheet);

            $rawData= DB::select('EXEC GenerateBalanceSheet ?,?', [$previousFinancialYear['start_date'],$previousFinancialYear['end_date']]);
            $balanceSheetData_previous = json_decode(json_encode($rawData), true);

            $formattedData_previous = $this->buildHierarchy($balanceSheetData_previous);
            $previousYearBalanceSheet=$formattedData_previous;
            $previousYearBalanceSheet['year']=date('Y',strtotime($previousFinancialYear['start_date']));
            $data['balance_sheet_previous']= array_values($previousYearBalanceSheet);
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    function getPreviousFinancialYearDates($date) {
        // Financial year starts in April and ends in March
        $financialYearStartMonth = 7;
        $financialYearEndMonth = 6;

        // Convert the given date to a DateTime object
        $givenDate = new DateTime($date);

        // Get the year and month of the given date
        $year = (int)$givenDate->format('Y');
        $month = (int)$givenDate->format('m');

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
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $f_end_date=date('Y-m-d',strtotime($financialYear->end_date));
        $f_start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $end_date=date('Y-m-d',strtotime($request->end_date));
        $start_date=date('Y-m-d',strtotime($request->start_date));
        $account_codes=$request->account_codes ? implode(',', $request->account_codes) : NULL;
        //return $data;
        if ($this->isInFinancialYear($start_date, $end_date, $f_start_date, $f_end_date)) {
            //return $data;
            try {
                DB::beginTransaction();
                $rawData = DB::select('EXEC GeneratePayableReceivable ?,?', [$start_date, $end_date]);
                $payablesheet = json_decode(json_encode($rawData), true);


                $data['payable_receivable'] = $payablesheet;
                DB::commit();
                return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
            } catch (\Exception $e) {
                DB::rollBack();
                return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
            }
        }else{
            return resp(0, 'Dates are not within the financial year.', Response::HTTP_EXPECTATION_FAILED);
        }
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
            ->groupBy('journal_voucher_details.nominal_id', 'chart_of_accounts.name', 'journal_vouchers.date','journal_voucher_details.detail')
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
        ]);

        // Set start and end dates
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Fetch budgeted amounts grouped by head_id from project_budget_details
        $budgetData = ProjectBudgetDetail::select(
            'head_id', // Grouping by head_id
            DB::raw('SUM(requested_funds) as total_budgeted')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('head_id') // Ensure head_id is not null
            ->groupBy('head_id')
            ->get()
            ->keyBy('head_id');

        // Fetch actual payables grouped by head_id
        $payableData = FinanceBillDetail::select(
            'finance_bill_details.head_id',
            DB::raw('SUM(finance_bill_details.total) as total_payable')
        )
            ->join('finance_bills', 'finance_bill_details.bill_id', '=', 'finance_bills.id')
            ->whereBetween('finance_bills.date', [$startDate, $endDate])
            ->whereNotNull('finance_bill_details.head_id')
            ->whereNotNull('finance_bill_details.total')
            ->groupBy('finance_bill_details.head_id')
            ->get()
            ->keyBy('head_id');

        // Fetch actual receivables grouped by head_id
        $receivableData = CustomerInvoiceDetail::select(
            'customer_invoice_details.item_coa as head_id', // Assuming item_coa maps to head_id
            DB::raw('SUM(customer_invoice_details.total) as total_receivable')
        )
            ->join('customer_invoices', 'customer_invoice_details.customer_invoice_id', '=', 'customer_invoices.id')
            ->whereBetween('customer_invoices.date', [$startDate, $endDate])
            ->whereNotNull('customer_invoice_details.item_coa') // Ensure item_coa (head_id) is not null
            ->whereNotNull('customer_invoice_details.total')
            ->groupBy('customer_invoice_details.item_coa')
            ->get()
            ->keyBy('head_id');

        // Combine keys from all datasets to get unique head_ids
        $headIds = $budgetData->keys()
            ->merge($payableData->keys())
            ->merge($receivableData->keys())
            ->unique();

        // Fetch chart of account details
        $chartOfAccounts = ChartOfAccount::whereIn('id', $headIds)
            ->get()
            ->keyBy('id');

        // Calculate variance for each head_id
        $varianceReport = [];

        // Combine data to calculate variance
        foreach ($headIds as $headId) {
            $budgetedAmount = $budgetData->get($headId)->total_budgeted ?? 0;
            $actualPayable = $payableData->get($headId)->total_payable ?? 0;
            $actualReceivable = $receivableData->get($headId)->total_receivable ?? 0;
            $actualTotal = $actualReceivable - $actualPayable; // Net effect of receivables and payables

            $variance = $actualTotal - $budgetedAmount;
            $variancePercentage = $budgetedAmount != 0 ? ($variance / $budgetedAmount) * 100 : 0;

            // Get chart of account details
            $chartAccount = $chartOfAccounts->get($headId);

            $varianceReport[] = [
                'head_id' => $headId,
                'chart_of_account' => $chartAccount, // Includes details like name, description
                'budgeted_amount' => $budgetedAmount,
                'actual_total' => $actualTotal,
                'variance' => $variance,
                'variance_percentage' => $variancePercentage,
            ];
        }

        return response()->json([
            'variance_report' => $varianceReport,
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
        $financialYear=FinancialYear::query()->where('status',1)->first();
        $f_end_date=date('Y-m-d',strtotime($financialYear->end_date));
        $f_start_date=date('Y-m-d',strtotime($financialYear->start_date));
        $end_date=date('Y-m-d',strtotime($request->end_date));
        $start_date=date('Y-m-d',strtotime($request->start_date));
        $account_codes=$request->account_codes ? implode(',', $request->account_codes) : NULL;
        //return $data;
        if ($this->isInFinancialYear($start_date, $end_date, $f_start_date, $f_end_date)) {
            try {
                DB::beginTransaction();
                $data['general_journal_bpv']= DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date,$end_date,$account_codes,'BPV']);
                $data['general_journal_cpv']= DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date,$end_date,$account_codes,'CPV']);
                $data['general_journal_brv']= DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date,$end_date,$account_codes,'BRV']);
                $data['general_journal_crv']= DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date,$end_date,$account_codes,'CRV']);
                $data['general_journal_jv']= DB::select('EXEC GenerateGeneralJournalReport ?,?,?,?', [$start_date,$end_date,$account_codes,'JV']);

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
    function isInFinancialYear($startDate, $endDate, $financialYearStart, $financialYearEnd) {
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
        $start_date=date('Y-m-d',strtotime($request->start_date));
        $end_date=date('Y-m-d',strtotime($request->end_date));
        $account_codes = !empty($account_codes) ? implode(',', $account_codes) : NULL;
        $account_codes_array = !empty($account) ? $account : [];


        //return $data;
        try {
            DB::beginTransaction();
            $result= DB::select('EXEC GenerateGeneralLedgerReport ?,?,?', [$start_date,$end_date,$account_codes]);
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
            $ledgerTransaction= [];
            if ($account_codes_array){
                foreach ($account_codes_array as $key => $code){
                    $AccountDetail = ChartOfAccount::query()->where('code',$code)->first();
                    $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts, $AccountDetail['parent_id']);
                }
            }else{
                $ledgerTransaction[] = $this->buildGeneralLedgerHierarchy($accounts);
            }


            $data['general_ledger']=$ledgerTransaction ;
            $data['general_ledger'] = $data['general_ledger'][0];
            DB::commit();
            return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    function buildGeneralLedgerHierarchy($entries, $parentId = 0) {
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
    function mapAccountCodeToId($entries, $accountCode) {
        foreach ($entries as $entry) {
            if ($entry['account_code'] == $accountCode) {
                return $entry['account_id'];
            }
        }
        return null; // Handle case where account code is not found
    }

    function findAndBuildHierarchyByCode($entries, $startAccountCodes = []) {
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

    public function itemVariantReport(Request $request)
    {
        $data['itemVariantList']=ItemVariant::query()->with('item','assignToEmploy','assignToDept')->whereNotNull('assign_to_emp')->orWhereNotNull('assign_to_dept')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }


}
