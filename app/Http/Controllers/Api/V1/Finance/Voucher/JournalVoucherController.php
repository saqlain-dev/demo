<?php

namespace App\Http\Controllers\Api\V1\Finance\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Finance\BankInfo;
use App\Models\Finance\FinanceBill\FinanceBillDetail;
use App\Models\Finance\Voucher\GeneralLedgerDetail;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\JournalVoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class JournalVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'JVs_view',
        ]);

        $data['listing'] = JournalVoucher::with('JournalVoucherDetail.NominalId')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'JVs_create',
        ]);

        $request->validate([
            'voucher_type' => 'required',
            'date' => 'required',
            'financial_year' => 'required',
            'amount' => 'required',
            'narration' => 'required',
            'instrument_id' => 'required',
            'voucher_from' => 'required',
            'payable_to' => 'required',
            'bank_account' => 'required',
        ]);
        $credits = $this->input['credits'];
        $debits = $this->input['debits'];
        unset($this->input['credits']);
        unset($this->input['debits']);
        $statement = DB::select("SELECT IDENT_CURRENT('journal_vouchers') as nextID");
        $this->input['voucher_id']='JV-'.sprintf('%04d', $statement[0]->nextID);
        try {
            DB::beginTransaction();
            $item = JournalVoucher::query()->create($this->input);
            if ($item){
                foreach ($debits as $debit) {
                    $debit_insert=array(
                        'journal_voucher_id'=>$item->id,
                        'voucher_type'=>$item->voucher_type,
                        'voucher_type_id'=>$item->voucher_type_id,
                        'date'=>$item->date,
                        'financial_year'=>$item->financial_year,
                        'nominal_id'=>$debit['account_id'],
                        'nominal_class'=>$debit['nominal_class'] ?? null,
                        'nominal_class_id'=>$debit['nominal_class_id'] ?? null,
                        'credit'=>0,
                        'debit'=>$debit['amount'],
                        'detail'=>$debit['description'],
                        'created_by'=>$item->created_by,
                        'vendor_id'=>$item->vendor_id,
                        'project_id'=>$item->project_id
                    );
                    JournalVoucherDetail::query()->create($debit_insert);
                }

                foreach ($credits as $credit) {
                    $credit_insert=array(
                        'journal_voucher_id'=>$item->id,
                        'voucher_type'=>$item->voucher_type,
                        'voucher_type_id'=>$item->voucher_type_id,
                        'date'=>$item->date,
                        'financial_year'=>$item->financial_year,
                        'nominal_id'=>$credit['account_id'],
                        'nominal_class'=>$credit['nominal_class'] ?? null,
                        'nominal_class_id'=>$credit['nominal_class_id'] ?? null,
                        'credit'=>$credit['amount'],
                        'debit'=>0,
                        'detail'=>$credit['description'],
                        'created_by'=>$item->created_by,
                        'vendor_id'=>$item->vendor_id,
                        'project_id'=>$item->project_id
                    );
                    JournalVoucherDetail::query()->create($credit_insert);
                }
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'JVs_view',
        ]);

        $data['voucher'] = JournalVoucher::query()->with(['JournalVoucherDetail.NominalId', 'BankAccount'])->findOrFail($id);
        //$data['voucher'] = $journalVoucher->load('JournalVoucherDetail');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalVoucher $journalVoucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'JVs_delete',
        ]);

        $journalVoucher = JournalVoucher::query()->findOrFail($id);
        if ($journalVoucher->is_verified == 0){
            //dd($journalVoucher->is_verified);
            $journalVoucher->JournalVoucherDetail()->delete();
            $item = $journalVoucher->delete();
            return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
        }
        else {
            return resp('1', 'Voucher exist. Record Cannot be Deleted!', [], Response::HTTP_OK);
        }
    }
}
