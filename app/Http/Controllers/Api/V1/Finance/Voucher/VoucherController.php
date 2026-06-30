<?php

namespace App\Http\Controllers\Api\V1\Finance\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\DraftVoucherRequest;
use App\Http\Requests\SaveGlEntVoucherRequest;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\FinancialYear;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\ApprovalProcessName;
use App\Models\EmployeePayrollMaster;
use App\Models\Finance\Audit\AuditTrail;
use App\Models\Finance\Audit\AuditTrailLog;
use App\Models\Finance\BankInfo;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\LasInvoice;
use App\Models\Finance\TaxManagement;
use App\Models\Finance\Voucher\GeneralLedger;
use App\Models\Finance\Voucher\GeneralLedgerDetail;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\Voucher;
use App\Models\Finance\Voucher\VoucherAttachment;
use App\Models\Finance\Voucher\VoucherDetail;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Invoice;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Reimbursement;
use App\Models\Type;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'un_vouchers_view',
            'journal_vouchers_view',
            'manage_audit_accounting_bookkeeping',
        ]);

       // $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount','auditTrail.verifiedBy')->whereNot('VoucherType','JV')->get();

        $query = Voucher::query()
            ->with('vendorDetail','ledger.ledgerDetail.chartOfAccount','auditTrail.verifiedBy')
            ->whereNot('VoucherType','JV');
        $query->when($request->filled('IsPosted'), function($q) use ($request) {
            $q->where('IsPosted', $request->IsPosted);
        });
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('Date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $data['voucher_listing'] = $query->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function voucherDropDown()
    {
        $data['classHeads'] = HeadClass::query()->get();
        $data['banks'] = BankInfo::query()->with('HeadId')->get();
        $data['payment_vouchers'] = Type::getTypeValues('payment-voucher');
        $data['receipt_vouchers'] = Type::getTypeValues('receipt-voucher');
        $data['journal_vouchers'] = Type::getTypeValues('journal-voucher');
        $data['projects'] = ProjectProfile::approvedProjects();
        $data['vendors'] = Vendor::all();
        $data['coc'] = ChartOfAccount::query()->with('ClassId.HeadClassId')->where('approval_status',1)->get();
        $data['financial_years'] = FinancialYear::query()->with('financialYear')->get();
        $data['tax_settings']=TaxManagement::query()->with('TaxType','TaxComputation','CountryId','taxScope','taxGroup')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVoucherRequest $request)
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'un_vouchers_create',
        ]);

        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $lastInsertedId = null;
            $parameters = [
                $validated['VoucherType'],
                $validated['VoucherTypeID'],
                date('Y-m-d', strtotime($validated['Date'])),
                $validated['FinancialYear'],
                $validated['Amount'],
                $validated['narration'] ?? null,
                $validated['Instrument_Id'] ?? null,
                $validated['vendor_id'] ?? null,
                $validated['payable_to'] ?? null,
                $validated['bank_account'] ?? null,
                $validated['VoucherFrom'] ?? null,
                null,
                null, // Placeholder for Project_ID if needed, replace with actual value
                auth()->user()->name,
                $validated['VerifiedBy'] ?? null,
                $validated['IsVerified'] ?? 0,
                $validated['PostedBy'] ?? null,
                $validated['IsPosted'] ?? 0,
                $validated['tax_section'] ?? null,
                $validated['tax_type'] ?? null,
                $validated['tax_rate'] ?? 0,
                $validated['tax_amount'] ?? 0,
                $validated['s_tax_section'] ?? null,
                $validated['s_tax_type'] ?? null,
                $validated['s_tax_rate'] ?? 0,
                $validated['s_tax_amount'] ?? 0,
                auth()->user()->id,
                now()->format('Y-m-d H:i:s') // Placeholder for created_at timestamp
            ];



            $query = 'DECLARE @LastInsertedId INT; ' .
                'EXEC sp_InsertVoucher ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?, @LastInsertedId OUTPUT; ';
           DB::connection('sqlsrv')->statement($query, $parameters);
           $voucherID= Voucher::max('id');
           $voucherDetail=Voucher::query()->where('id',$voucherID)->first();
           $voucherFrom=$validated['VoucherFrom'];
           $voucherFromID=$validated['VoucherFromID'];

           if($voucherDetail){
               /*$updateJvStatus = array('is_verified' => 1);
               JournalVoucher::query()->where('id',$request['jv_id'])->update($updateJvStatus);*/


            // Voucher From Insert
               $voucher_from= $voucherFrom;
               $voucher_fromID=explode(',',$voucherFromID);
               foreach($voucher_fromID as $key => $fromID){
                   $insetVoucherFrom=array(
                       'voucher_id'=>$voucherID,
                       'VoucherFrom'=>$voucher_from,
                       'VoucherFromID'=>$fromID
                   );
                   VoucherDetail::query()->insert($insetVoucherFrom);
               }

               // Voucher From Insert END

               if($validated['VoucherFrom'] == 'JV'){
                   $jvVoucherDetail=Voucher::query()->where('id',$voucherID)->first();
                   $jvFromIds=VoucherDetail::query()->where('voucher_id',$voucherID)->pluck('VoucherFromID');
                   if( $jvVoucherDetail->VoucherFrom == 'PAYROLL'){
                       EmployeePayrollMaster::query()->whereIn('id',$jvFromIds)->update(array('is_voucher_posted'=>1));
                   }
                   if( $jvVoucherDetail->VoucherFrom  == 'LOAN'){
                       AdvanceSalary::query()->whereIn('id',$jvFromIds)->update(['is_voucher_posted'=>1, 'voucher_id' =>$voucherID]);
                   }
                   if(($jvVoucherDetail->VoucherFrom == 'WO' || $jvVoucherDetail->VoucherFrom == 'CC' || $jvVoucherDetail->VoucherFrom == 'PO') ){
                       Invoice::query()->whereIn('id',$jvFromIds)->update(array('is_voucher_posted'=>1));
                   }
                   if(($jvVoucherDetail->VoucherFrom == 'Reimbursements') ){
                       Reimbursement::query()->whereIn('id',$jvFromIds)->update(array('is_voucher_posted'=>1));
                   }
                   if(($jvVoucherDetail->VoucherFrom == 'Travel Expense') ){
                       ClaimTravelExpense::query()->whereIn('id',$jvFromIds)->update(array('is_voucher_posted'=>1));
                   }
                   if(($jvVoucherDetail->VoucherFrom == 'Court Expense') ){
                       CourtExpense::query()->whereIn('id',$jvFromIds)->update(array('is_voucher_posted'=>1));
                   }
               }

                $ledger=array(
                    'VoucherID'=>$voucherDetail['VoucherID'],
                    'VoucherType'=>$voucherDetail['VoucherType'],
                    'VoucherTypeID'=>$voucherDetail['VoucherTypeID'],
                    'Date'=>$voucherDetail['Date'],
                    'FinancialYear'=>$voucherDetail['FinancialYear'],
                    'Amount'=>$voucherDetail['Amount'],
                    'narration'=>$voucherDetail['narration'],
                    'CreatedBy'=>$voucherDetail['CreatedBy'],
                    'vendor_id'=>$voucherDetail['vendor_id'],
                    'project_id'=>$voucherDetail['project_id'],
                    'voucher_no'=>$voucherDetail['id'],
                    'IsVerified'=>0,
                    'IsPosted'=>0,
                );
               try {
               $ledger= GeneralLedger::query()->create($ledger);
               } catch (\Exception $e) {
                    DB::rollBack();
                    return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
                }

               foreach ($validated['debits'] as $debit) {
                   $debit_insert=array(
                       'Gl_Id'=>$ledger->id,
                       'VoucherID'=>$ledger->VoucherID,
                       'VoucherType'=>$ledger->VoucherType,
                       'VoucherTypeID'=>$ledger->VoucherTypeID,
                       'Date'=>$ledger->Date,
                       'FinancialYear'=>$ledger->FinancialYear,
                       'NominalID'=>$debit['account_id'],
                       'NominalClass'=>$debit['NominalClass'] ?? null,
                       'NominalClassID'=>$debit['NominalClassID'] ?? null,
                       'Credit'=>0,
                       'Debit'=>$debit['amount'],
                       'detail'=>$debit['description'],
                       'CreatedBy'=>$ledger->CreatedBy,
                       'vendor_id'=>$ledger->vendor_id,
                       'project_id'=>$ledger->project_id
                   );
                    GeneralLedgerDetail::query()->create($debit_insert);
               }

               foreach ($validated['credits'] as $credit) {
                   $credit_insert=array(
                       'Gl_Id'=>$ledger->id,
                       'VoucherID'=>$ledger->VoucherID,
                       'VoucherType'=>$ledger->VoucherType,
                       'VoucherTypeID'=>$ledger->VoucherTypeID,
                       'Date'=>$ledger->Date,
                       'FinancialYear'=>$ledger->FinancialYear,
                       'NominalID'=>$credit['account_id'],
                       'NominalClass'=>$credit['NominalClass'] ?? null,
                       'NominalClassID'=>$credit['NominalClassID'] ?? null,
                       'Credit'=>$credit['amount'],
                       'Debit'=>0,
                       'detail'=>$credit['description'],
                       'CreatedBy'=>$ledger->CreatedBy,
                       'vendor_id'=>$ledger->vendor_id,
                       'project_id'=>$ledger->project_id
                   );
                   GeneralLedgerDetail::query()->create($credit_insert);
               }
           }
           DB::commit();
            return resp(1, 'Successful!', $voucherDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function draftVoucher(DraftVoucherRequest $request)
    {

        $validated = $request->validated();
        try {
            DB::beginTransaction();
            $lastInsertedId = null;
            $parameters = [
                $validated['VoucherType'],
                $validated['VoucherTypeID'],
                date('Y-m-d', strtotime($validated['Date'])),
                $validated['FinancialYear'],
                $validated['Amount'],
                $validated['narration'] ?? null,
                $validated['Instrument_Id'] ?? null,
                $validated['vendor_id'] ?? null,
                $validated['payable_to'] ?? null,
                $validated['bank_account'] ?? null,
                null,
                null,
                null, // Placeholder for Project_ID if needed, replace with actual value
                auth()->user()->name,
                $validated['VerifiedBy'] ?? null,
                $validated['IsVerified'] ?? 0,
                $validated['PostedBy'] ?? null,
                $validated['IsPosted'] ?? 0,
                auth()->user()->id,
                now()->format('Y-m-d H:i:s') // Placeholder for created_at timestamp
            ];


            $query = 'DECLARE @LastInsertedId INT; ' .
                'EXEC sp_InsertVoucher ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?, @LastInsertedId OUTPUT; ';
            $result =DB::connection('sqlsrv')->statement($query, $parameters);
            //$voucherID = $result[0]->LastInsertedId;
           $voucherID= Voucher::max('id');
           $voucherDetail=Voucher::query()->where('id',$voucherID)->first();
           if($voucherDetail){
            $voucher_from= explode(',',$this->input['VoucherFrom']);
            $voucher_fromID=explode(',',$this->input['VoucherFromID']);
            foreach($voucher_fromID as $key => $fromID){
                $insetVoucherFrom=array(
                    'voucher_id'=>$voucherID,
                    'VoucherFrom'=>$voucher_from[$key],
                    'VoucherFromID'=>$fromID
                );
                VoucherDetail::query()->insert($insetVoucherFrom);
            }
           }
           DB::commit();
            return resp(1, 'Successful!', $voucherDetail->load('voucherDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveVoucher(SaveGlEntVoucherRequest $request)
    {


        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $lastInsertedId = null;
            $voucherID=$validated['voucher_id'];
            $voucherDetail=Voucher::query()->where('id',$voucherID)->first();
            if($voucherDetail){
                if( $voucherDetail['VoucherFrom'] == 'PAYROLL' && $voucherDetail['VoucherFromID'] != ""){
                    EmployeePayrollMaster::query()->where('id',$voucherDetail['VoucherFromID'])->update(array('is_voucher_posted'=>1));
                }
                if( $voucherDetail['VoucherFrom'] == 'LOAN' && $voucherDetail['VoucherFromID'] != ""){
                    AdvanceSalary::query()->where('id', $voucherDetail['VoucherFromID'])->update(['is_voucher_posted'=>1, 'voucher_id' =>$voucherID]);
                }
                if(($voucherDetail['VoucherFrom'] == 'WO' || $voucherDetail['VoucherFrom'] == 'CC' || $voucherDetail['VoucherFrom'] == 'PO')  && $voucherDetail['VoucherFromID'] != ""){
                    Invoice::query()->where('id',$voucherDetail['VoucherFromID'])->update(array('is_voucher_posted'=>1));
                }
                $ledger=array(
                    'VoucherID'=>$voucherDetail['VoucherID'],
                    'VoucherType'=>$voucherDetail['VoucherType'],
                    'VoucherTypeID'=>$voucherDetail['VoucherTypeID'],
                    'Date'=>$voucherDetail['Date'],
                    'FinancialYear'=>$voucherDetail['FinancialYear'],
                    'Amount'=>$voucherDetail['Amount'],
                    'narration'=>$voucherDetail['narration'],
                    'CreatedBy'=>$voucherDetail['CreatedBy'],
                    'vendor_id'=>$voucherDetail['vendor_id'],
                    'project_id'=>$voucherDetail['project_id'],
                    'voucher_no'=>$voucherDetail['id'],
                    'IsVerified'=>0,
                    'IsPosted'=>0,
                );
                try {
                    $ledger= GeneralLedger::query()->create($ledger);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
                }

                foreach ($validated['debits'] as $debit) {
                    $debit_insert=array(
                        'Gl_Id'=>$ledger->id,
                        'VoucherID'=>$ledger->VoucherID,
                        'VoucherType'=>$ledger->VoucherType,
                        'VoucherTypeID'=>$ledger->VoucherTypeID,
                        'Date'=>$ledger->Date,
                        'FinancialYear'=>$ledger->FinancialYear,
                        'NominalID'=>$debit['account_id'],
                        'NominalClass'=>$debit['NominalClass'] ?? null,
                        'NominalClassID'=>$debit['NominalClassID'] ?? null,
                        'Credit'=>0,
                        'Debit'=>$debit['amount'],
                        'detail'=>$debit['narration'],
                        'CreatedBy'=>$ledger->CreatedBy,
                        'vendor_id'=>$ledger->vendor_id,
                        'project_id'=>$ledger->project_id
                    );
                    GeneralLedgerDetail::query()->create($debit_insert);
                }

                foreach ($validated['credits'] as $credit) {
                    $credit_insert=array(
                        'Gl_Id'=>$ledger->id,
                        'VoucherID'=>$ledger->VoucherID,
                        'VoucherType'=>$ledger->VoucherType,
                        'VoucherTypeID'=>$ledger->VoucherTypeID,
                        'Date'=>$ledger->Date,
                        'FinancialYear'=>$ledger->FinancialYear,
                        'NominalID'=>$credit['account_id'],
                        'NominalClass'=>$credit['NominalClass'] ?? null,
                        'NominalClassID'=>$credit['NominalClassID'] ?? null,
                        'Credit'=>$credit['amount'],
                        'Debit'=>0,
                        'detail'=>$credit['narration'],
                        'CreatedBy'=>$ledger->CreatedBy,
                        'vendor_id'=>$ledger->vendor_id,
                        'project_id'=>$ledger->project_id
                    );
                    GeneralLedgerDetail::query()->create($credit_insert);
                }
            }
            DB::commit();
            return resp(1, 'Successful!', $voucherDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Voucher $voucher)
    {
        $this->authorizeAny([
            'un_vouchers_view',
            'journal_vouchers_view',
            'manage_audit_grant_management',
            'manage_audit_accounting_bookkeeping',
        ]);
        $voucher=$voucher->load('ledger.ledgerDetail.chartOfAccount','ledger.ledgerDetail.headClass','vendorDetail','BankAccount','auditTrail.verifiedBy','voucherDetail','voucherAttachments');




        if($voucher){
            if($voucher->VoucherType == 'JV'){
                $VoucherFromID=VoucherDetail::query()->where('voucher_id',$voucher->id)->pluck('VoucherFromID');
                if($voucher->VoucherFrom  == 'Invoice'){
                    $invoices = Invoice::query()
                        ->whereIn('id', $VoucherFromID) // Fetch multiple invoices
                        ->with([
                            'grn' => [
                                'grnItem',
                                'poDetails' => [
                                    'tenderDetails.purchaseRequest',
                                    'rfqDetail' => ['vendor_quotations', 'purchase_request']
                                ]
                            ],
                            'vendorDetail',
                            'consultantContract.CcItems',
                            'workOrder' => [
                                'tenderDetail.purchaseRequest',
                                'rfqDetail.purchase_request'
                            ],
                            'rfq.disposeRequest',
                            'ProjectId'
                        ])
                        ->get();
                    $voucher->invoices = $invoices;
                }
                if($voucher->VoucherFrom  == 'Reimbursements'){

                    $reimbursements = Reimbursement::query()
                        ->whereIn('id', $VoucherFromID)
                        ->with(['PrId'])
                        ->get();

                    $voucher->reimbursements = $reimbursements; // Assign the collection to the voucher

                }
                if($voucher->VoucherFrom  == 'Travel Expense'){


                    $claimTravelExpenses = ClaimTravelExpense::query()
                        ->whereIn('id', $VoucherFromID)
                        ->with(['PrId'])
                        ->get();

                    $voucher->claimTravelExpenses = $claimTravelExpenses; // Assign the collection to the voucher

                }
                if($voucher->VoucherFrom  == 'Court Expense'){

                    $courtExpenses = CourtExpense::query()
                        ->whereIn('id', $VoucherFromID)
                        ->with(['PrId'])
                        ->get();

                    $voucher->courtExpenses = $courtExpenses; // Assign the collection to the voucher

                }
                if($voucher->VoucherFrom  == 'LAS-INV'){

                    $lasInvoices = LasInvoice::query()
                        ->whereIn('id', $VoucherFromID)
                        ->with(['nofo'])
                        ->get();

                    $voucher->lasInvoices = $lasInvoices; // Assign the collection to the voucher

                }
            }else{


                $voucher->load('jvVoucher.jvVoucherDetail');
                if ($voucher->jvVoucher !== null) {
                    $VoucherFromID = VoucherDetail::query()->where('voucher_id', $voucher->jvVoucher->voucher_id)->pluck('VoucherFromID');

                    if ($voucher->jvVoucher) {

                        if ($voucher->jvVoucher->VoucherFrom == 'Invoice') {
                            $invoices = Invoice::query()
                                ->whereIn('id', $VoucherFromID) // Fetch multiple invoices
                                ->with([
                                    'grn' => [
                                        'grnItem',
                                        'poDetails' => [
                                            'tenderDetails.purchaseRequest',
                                            'rfqDetail' => ['vendor_quotations', 'purchase_request']
                                        ]
                                    ],
                                    'vendorDetail',
                                    'consultantContract.CcItems',
                                    'workOrder' => [
                                        'tenderDetail.purchaseRequest',
                                        'rfqDetail.purchase_request'
                                    ],
                                    'rfq.disposeRequest',
                                    'ProjectId'
                                ])
                                ->get();
                            $voucher->jvVoucher->invoice = $invoices;


                        }
                        if ($voucher->jvVoucher->VoucherFrom == 'Reimbursements') {

                            $reimbursements = Reimbursement::query()
                                ->whereIn('id', $VoucherFromID)
                                ->with(['PrId'])
                                ->get();

                            $voucher->jvVoucher->reimbursement = $reimbursements; // Assign the collection to the voucher
                        }
                        if ($voucher->jvVoucher->VoucherFrom == 'Travel Expense') {

                            $claimTravelExpenses = ClaimTravelExpense::query()
                                ->whereIn('id', $VoucherFromID)
                                ->with(['PrId'])
                                ->get();

                            $voucher->jvVoucher->claimTravelExpense = $claimTravelExpenses; // Assign the collection to the voucher
                        }
                        if ($voucher->jvVoucher->VoucherFrom == 'Court Expense') {

                            $courtExpenses = CourtExpense::query()
                                ->whereIn('id', $VoucherFromID)
                                ->with(['PrId'])
                                ->get();

                            $voucher->jvVoucher->courtExpense = $courtExpenses; // Assign the collection to the voucher
                        }
                        if ($voucher->jvVoucher->VoucherFrom == 'LAS-INV') {


                            $lasInvoices = LasInvoice::query()
                                ->whereIn('id', $VoucherFromID)
                                ->with(['nofo'])
                                ->get();

                            $voucher->jvVoucher->lasInvoice = $lasInvoices; // Assign the collection to the voucher
                        }
                        if ($voucher->jvVoucher->VoucherFrom == 'ATR') {


                            $airTravelRequest= AirTravelRequest::query()
                                ->whereIn('id', $VoucherFromID)
                                ->with(['items','department','airlineCategory','created_by'])
                                ->get();

                            $voucher->jvVoucher->airTravlRequest = $airTravelRequest; // Assign the collection to the voucher
                        }

                    }
                }
            }


        }

        $data['voucher_detail'] = $voucher;
        $data['approval_request']=getNextApproval(41,auth()->user()->designation_id,$voucher->id);
        $data['approval_request_status']=checkApprovalRequestStatus(41,$voucher->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getPendingVouchers()
    {
        $this->authorizeAny([
            'manage_audit_grant_management',
        ]);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->where('approval_status',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function getJvVouchers()
    {
        //$data['jv_voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->where('VoucherType','JV')->get();
        $data['jv_voucher_listing'] = Voucher::query()
            ->with([
                'vendorDetail',
                'ledger.ledgerDetail.chartOfAccount',
                'reverseVoucher' => function ($query) {
                    $query->select('id', 'voucher_id', 'VoucherFrom', 'VoucherFromID')->with('voucherDetail');
                }
            ])
            ->where('VoucherType', 'JV')
            //->where('id',139)
            ->get();


        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function getPendingPostedVouchers()
    {
        $this->authorizeAny([
            'manage_posted_vouchers',
            'manage_audit_accounting_bookkeeping',
        ]);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->where('IsPosted',0)->where('IsVerified',1)->whereNot('VoucherType','JV')->where('approval_status',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function getPostedVoucherListing()
    {
        $this->authorizeAny([
            'manage_posted_vouchers',
            'manage_audit_accounting_bookkeeping',
        ]);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','auditTrail','ledger.ledgerDetail.chartOfAccount')->where('IsPosted','!=',1)->where('IsVerified',1)->where('approval_status',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getAuditVouchers(Request $request)
    {
        $this->authorizeAny([
            'manage_posted_vouchers',
            'manage_audit_accounting_bookkeeping',
        ]);

        $query = Voucher::query()
            ->with('vendorDetail', 'ledger.ledgerDetail.chartOfAccount');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('Date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        $data['voucher_listing'] = $query->get();



        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function getAuditVoucherApproval()
    {
        $this->authorizeAny([
            'manage_posted_vouchers',
            'manage_audit_accounting_bookkeeping',
        ]);
        set_time_limit(120);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->where('IsPosted','!=',1)->get();
        $data['invoice_list'] = Invoice::whereNot('invoice_status',1)->get();
        $approvalProcessLists=ApprovalProcessName::query()->whereNotNull('module_path')->get();
        $approval_process_listing=array();
        foreach($approvalProcessLists as $appProcess){

            $approval_request=getAllNextApproval($appProcess['id'],auth()->user()->designation_id);
            //$approval_request['module_path']=$appProcess['module_path'];
            if($approval_request){
                foreach($approval_request as $key => $requestApp){
                    $requestApp->module_path=$appProcess['module_path'];
                    $requestApp->approval_process_name=$appProcess['approval_process_name'];
                    $approval_process_listing[]=$requestApp;
                }


            }

        }
        $data['approval_process_listing']=$approval_process_listing;

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function verifiedVoucher(Request $request,Voucher $voucher)
    {
        $request->validate([
            'IsVerified' => 'required|integer'
        ]);
        try {
            DB::beginTransaction();
            $voucher->IsVerified=$request->IsVerified;
            $voucher->VerifiedBy=auth()->user()->id;
            if($request->reason != ""){
                $voucher->reason=$request->reason;
            }
            $voucher->save();
            $voucher->refresh();
            if($voucher && $voucher->IsVerified == 1){
                $updateLedger=array(
                    'IsVerified'=>$request->IsVerified,
                    'VerifiedBy'=>auth()->user()->id,
                );
                GeneralLedger::query()->where('voucher_no',$voucher->id)->update($updateLedger);
            }

            $this->auditTrail($request,$voucher);
            $this->auditTrailLog($request,$voucher);
            DB::commit();
            return resp(1, 'Successful!', $voucher, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveAttachment($request,$folder){

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
    public function uploadAttachment(Request $request,Voucher $voucher)
    {
        $request->validate([
            'attachment_title' => 'required|string',
            'attachment' => 'required|file',
        ]);
        try {
            DB::beginTransaction();
            if($request->hasFile('attachment')) {
                $responce = $this->saveAttachment($request, 'VoucherAttachment');
                if ($responce) {
                    $attachmentPath = $responce;
                }
            }
            $attachment = VoucherAttachment::create([
                'voucher_id' => $voucher->id,
                'attachment_title' => $request->attachment_title,
                'attachment' => $attachmentPath,
            ]);
            DB::commit();
            return resp(1, 'Successful!', $attachment, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteVoucherAttachment(Request $request)
    {
        $request->validate([
            'attachment_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();
            $voucherAttachment=VoucherAttachment::query()->where('id',$request->attachment_id)->delete();
            DB::commit();
            return resp(1, 'Successful!', $voucherAttachment, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function deleteVoucher(Request $request)
    {
        $request->validate([
            'voucher_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $voucher = Voucher::find($request->voucher_id);

            if ($voucher && $voucher->approval_status != 1 && $voucher->IsVerified != 1) {

                // Delete voucher details only if voucher is deletable
                VoucherDetail::where('voucher_id', $voucher->id)->delete(); // or delete() if soft deletes

                $voucher->forceDelete();

                $ledger = GeneralLedger::where('voucher_no', $voucher->id)->first();
                if ($ledger) {
                    GeneralLedgerDetail::where('Gl_Id', $ledger->id)->forceDelete();
                    $ledger->forceDelete();
                }
                DB::commit();
                return resp(1, 'Successful!',[], Response::HTTP_OK);
            } else {
                DB::rollBack();
                return resp(0, 'Not deleted!', [], Response::HTTP_OK);
            }


        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function auditTrail($request,$voucher)
    {
        $insert=array(
            'voucher_id'=>$voucher->id,
            'IsVerified'=>$request->IsVerified,
            'VerifiedBy'=>$voucher->VerifiedBy,
            'reason'=>$request->reason,
        );

        AuditTrail::query()->create($insert);
    }

    public function getAuditTrailLog()
    {
        $data['audit_trail_log']=AuditTrailLog::query()->get();
        return resp(1,'', $data,Response::HTTP_OK);
    }

    public function auditTrailLog($request,$voucher)
    {

        $insert=array(
            'invoice_no'=>$voucher->Instrument_Id,
            'vendor_name'=>$voucher->payable_to,
            'submission_to_audit_date'=>date('Y-m-d',strtotime($voucher->created_at)),
            'amount'=>$voucher->Amount,
            'observation'=>$voucher->narration,
            'voucher_date'=>$voucher->Date,
            'voucher_no'=>$voucher->VoucherID,
        );
        if($voucher->Instrument_Id != ""){
            $invoice=Invoice::query()->with(['grn'=>['poDetails.rfqDetail.purchase_request.project'],'ProjectId','workOrder'=>['rfqDetail.purchase_request.project'],'rfq.purchase_request.project'])->where('invoice_number',$voucher->Instrument_Id)->first();

            $prfNumber = null;
            $prfDate= null;
            $projectName= null;
            $PoDate= null;

            $grn = optional($invoice)->grn;
            $poDetails = optional($grn)->poDetails;
            $rfqDetail = optional($poDetails)->rfqDetail;

            if ($rfqDetail && $rfqDetail->purchase_request) {
                $prfNumber = $invoice->grn->poDetails->rfqDetail->purchase_request->purchase_request_no;
                $prfDate = $invoice->grn->poDetails->rfqDetail->purchase_request->date;
                $projectName = $invoice->grn->poDetails->rfqDetail->purchase_request->project->project_name;
                $PoDate = $invoice->grn->poDetails->purchase_order_date;
            } elseif (
                optional(optional($invoice)->workOrder)->rfqDetail &&
                optional(optional(optional($invoice)->workOrder)->rfqDetail)->purchase_request
            ){
                $prfNumber = $invoice->workOrder->rfqDetail->purchase_request->purchase_request_no;
                $prfDate = $invoice->workOrder->rfqDetail->purchase_request->date;
                $projectName = $invoice->workOrder->rfqDetail->purchase_request->project->project_name;
            } elseif (
                optional(optional($invoice)->rfq)->purchase_request
            ) {
                $prfNumber = $invoice->rfq->purchase_request->purchase_request_no;
                $prfDate = $invoice->rfq->purchase_request->date;
                $projectName = $invoice->rfq->purchase_request->project->project_name;
            }


            $insert['invoice_received_date']=$invoice->invoice_date ?? NULL;
            $insert['prf_no']=$prfNumber ?? NULL;
            $insert['prf_date']=$prfDate ?? NULL;
            $insert['project']=$projectName ?? NULL;
            $insert['PoDate']=$PoDate ?? NULL;
        }


        AuditTrailLog::query()->create($insert);
    }
    public function postedVoucher(Request $request,Voucher $voucher)
    {
        $request->validate([
            'IsPosted' => 'required|integer'
        ]);
        try {
            DB::beginTransaction();
            $voucher->IsPosted=$request->IsPosted;
            $voucher->PostedBy=auth()->user()->id;
            $voucher->save();
            $voucher->refresh();
            if($voucher){
                $updateLedger=array(
                    'IsPosted'=>$request->IsPosted,
                    'PostedBy'=>auth()->user()->id,
                );
                GeneralLedger::query()->where('voucher_no',$voucher->id)->update($updateLedger);
            }

            DB::commit();
            return resp(1, 'Successful!', $voucher, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function bulkPostedVoucher(Request $request)
    {
        $request->validate([
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'integer|exists:vouchers,id'
        ]);
        try {
            DB::beginTransaction();
            Voucher::whereIn('id', $request->voucher_ids)->update(['IsPosted' => 1]);
            $voucherList=Voucher::whereIn('id', $request->voucher_ids)->get();
            if($voucherList){
                foreach($voucherList as $voucher) {
                    $updateLedger = array(
                        'IsPosted' => 1,
                        'PostedBy' => auth()->user()->id,
                    );
                    GeneralLedger::query()->where('voucher_no', $voucher->id)->update($updateLedger);
                }
            }

            DB::commit();
            return resp(1, 'Successful!', $voucher, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function createJournalVoucher(StoreVoucherRequest $request)
    {
        $this->authorizeAny([
            'journal-journal_vouchers_create',
        ]);

        $validated = $request->validated();

        try {
            DB::beginTransaction();
            $lastInsertedId = null;
            /*$parameters = [
                'JV',
                $validated['VoucherTypeID'],
                date('Y-m-d', strtotime($validated['Date'])),
                $validated['FinancialYear'],
                $validated['Amount'],
                $validated['narration'] ?? null,
                $validated['Instrument_Id'] ?? null,
                null,
                null, // Placeholder for Project_ID if needed, replace with actual value
                auth()->user()->name,
                $validated['VerifiedBy'] ?? null,
                $validated['IsVerified'] ?? 0,
                $validated['PostedBy'] ?? null,
                $validated['IsPosted'] ?? 0,
                auth()->user()->id,
                now()->format('Y-m-d H:i:s') // Placeholder for created_at timestamp
            ];*/
            $parameters = [
                'JV',
                $validated['VoucherTypeID'],
                date('Y-m-d', strtotime($validated['Date'])),
                $validated['FinancialYear'],
                $validated['Amount'],
                $validated['narration'] ?? null,
                $validated['Instrument_Id'] ?? null,
                null,
                $validated['payable_to'] ?? null,
                $validated['bank_account'] ?? null,
                $validated['VoucherFrom'] ?? null,
                $validated['VoucherFromID'] ?? null,
                null, // Placeholder for Project_ID if needed, replace with actual value
                auth()->user()->name,
                $validated['VerifiedBy'] ?? null,
                $validated['IsVerified'] ?? 0,
                $validated['PostedBy'] ?? null,
                $validated['IsPosted'] ?? 0,
                auth()->user()->id,
                now()->format('Y-m-d H:i:s') // Placeholder for created_at timestamp
            ];

            $query = 'DECLARE @LastInsertedId INT; ' .
                'EXEC sp_InsertVoucher ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?, @LastInsertedId OUTPUT; ';
            DB::connection('sqlsrv')->statement($query, $parameters);
            $voucherID= Voucher::max('id');
            $voucherDetail=Voucher::query()->where('id',$voucherID)->first();
            if($voucherDetail){
                $ledger=array(
                    'VoucherID'=>$voucherDetail['VoucherID'],
                    'VoucherType'=>$voucherDetail['VoucherType'],
                    'VoucherTypeID'=>$voucherDetail['VoucherTypeID'],
                    'Date'=>$voucherDetail['Date'],
                    'FinancialYear'=>$voucherDetail['FinancialYear'],
                    'Amount'=>$voucherDetail['Amount'],
                    'narration'=>$voucherDetail['narration'],
                    'CreatedBy'=>$voucherDetail['CreatedBy'],
                    'vendor_id'=>null,
                    'project_id'=>$voucherDetail['project_id'],
                    'voucher_no'=>$voucherDetail['id'],
                    'IsVerified'=>0,
                    'IsPosted'=>0,
                );

                try {
                    $ledger= GeneralLedger::query()->create($ledger);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
                }
                foreach ($validated['debits'] as $debit) {
                    $debit_insert=array(
                        'Gl_Id'=>$ledger->id,
                        'VoucherID'=>$ledger->VoucherID,
                        'VoucherType'=>$ledger->VoucherType,
                        'VoucherTypeID'=>$ledger->VoucherTypeID,
                        'Date'=>$ledger->Date,
                        'FinancialYear'=>$ledger->FinancialYear,
                        'NominalID'=>$debit['account_id'],
                        'NominalClass'=>$debit['NominalClass'],
                        'NominalClassID'=>$debit['NominalClassID'],
                        'Credit'=>0,
                        'Debit'=>$debit['amount'],
                        'detail'=>$debit['description'],
                        'CreatedBy'=>$ledger->CreatedBy,
                        'vendor_id'=>null,
                        'project_id'=>$ledger->project_id
                    );

                    GeneralLedgerDetail::query()->create($debit_insert);
                }

                foreach ($validated['credits'] as $credit) {
                    $credit_insert=array(
                        'Gl_Id'=>$ledger->id,
                        'VoucherID'=>$ledger->VoucherID,
                        'VoucherType'=>$ledger->VoucherType,
                        'VoucherTypeID'=>$ledger->VoucherTypeID,
                        'Date'=>$ledger->Date,
                        'FinancialYear'=>$ledger->FinancialYear,
                        'NominalID'=>$credit['account_id'],
                        'NominalClass'=>$credit['NominalClass'],
                        'NominalClassID'=>$credit['NominalClassID'],
                        'Credit'=>$credit['amount'],
                        'Debit'=>0,
                        'detail'=>$credit['description'],
                        'CreatedBy'=>$ledger->CreatedBy,
                        'vendor_id'=>null,
                        'project_id'=>$ledger->project_id
                    );
                    GeneralLedgerDetail::query()->create($credit_insert);
                }
            }
            DB::commit();
            return resp(1, 'Successful!', $voucherDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        //
    }
    public function sendVoucherForApproval(Voucher $item)
    {

        $approval_process_name=ApprovalProcessName::query()->where('id',41)->first();
        $approval_process=ApprovalProcess::query()->where('approval_process_id',41)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',41)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                if($approval_process_name->isFinancialApproval == 1){
                    if($approval->financialAmount < $item->Amount  ){
                        $insert['approval_status']=0;
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }else{
                        $Approval=ApprovalProcessList::query()->create($insert);
                    }
                }else{
                    $Approval=ApprovalProcessList::query()->create($insert);
                }

                sendNotification($approval['designation_id'],$approval_process_name->approval_process_name);


            }
            $update=array('approval_status'=>2);
            Voucher::query()->where('id',$item->id)->update($update);
            return resp(1,'Voucher request send for Approval.', [],Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Voucher approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
