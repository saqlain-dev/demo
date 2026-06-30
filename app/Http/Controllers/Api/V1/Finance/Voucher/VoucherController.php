<?php

namespace App\Http\Controllers\Api\V1\Finance\Voucher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Admin\FinancialYear;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\EmployeePayrollMaster;
use App\Models\Finance\BankInfo;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\Voucher\GeneralLedger;
use App\Models\Finance\Voucher\GeneralLedgerDetail;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\Voucher;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Invoice;
use App\Models\Program\Project\ProjectProfile;
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
    public function index()
    {
        $this->authorizeAny([
            'manage_payment_requests',
            'un_vouchers_view',
            'journal_vouchers_view',
            'manage_audit_accounting_bookkeeping',
        ]);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->get();
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
               $updateJvStatus = array('is_verified' => 1);
               JournalVoucher::query()->where('id',$request['jv_id'])->update($updateJvStatus);
               if( $validated['VoucherFrom'] == 'PAYROLL' && $validated['VoucherFromID'] != ""){
                   EmployeePayrollMaster::query()->where('id',$validated['VoucherFromID'])->update(array('is_voucher_posted'=>1));
               }
               if( $validated['VoucherFrom'] == 'LOAN' && $validated['VoucherFromID'] != ""){
                   AdvanceSalary::query()->where('id', $validated['VoucherFromID'])->update(['is_voucher_posted'=>1, 'voucher_id' =>$voucherID]);
               }
               if(($validated['VoucherFrom'] == 'WO' || $validated['VoucherFrom'] == 'CC' || $validated['VoucherFrom'] == 'PO')  && $validated['VoucherFromID'] != ""){
                   Invoice::query()->where('id',$validated['VoucherFromID'])->update(array('is_voucher_posted'=>1));
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

        $data['voucher_detail'] = $voucher->load('ledger.ledgerDetail.chartOfAccount','vendorDetail','BankAccount');
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
    public function getPendingPostedVouchers()
    {
        $this->authorizeAny([
            'manage_posted_vouchers',
            'manage_audit_accounting_bookkeeping',
        ]);

        $data['voucher_listing'] = Voucher::query()->with('vendorDetail','ledger.ledgerDetail.chartOfAccount')->where('IsPosted',0)->where('IsVerified',1)->where('approval_status',1)->get();
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
            DB::commit();
            return resp(1, 'Successful!', $voucher, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
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
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            Voucher::query()->where('id',$item->id)->update($update);
            return resp(1,'Voucher request send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Voucher approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
