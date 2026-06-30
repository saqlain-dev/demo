<?php

namespace App\Http\Controllers\Api\V1\Finance\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\JournalVoucherDetail;
use App\Models\Finance\Voucher\UnpostedVoucher;
use App\Models\Finance\Voucher\UnpostedVoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UnpostedVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = UnpostedVoucher::with('UnpostedVoucherDetail')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'journal_voucher_id' => 'required',
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
        try {
            DB::beginTransaction();
            $item = UnpostedVoucher::query()->create($this->input);
            if ($item){
                foreach ($debits as $debit) {
                    $debit_insert=array(
                        'unposted_voucher_id'=>$item->id,
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
                    UnpostedVoucherDetail::query()->create($debit_insert);
                }

                foreach ($credits as $credit) {
                    $credit_insert=array(
                        'unposted_voucher_id'=>$item->id,
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
                    UnpostedVoucherDetail::query()->create($credit_insert);
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
    public function show(UnpostedVoucher $unpostedVoucher)
    {
        $data['upvoucher'] = $unpostedVoucher->load('UnpostedVoucherDetail');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnpostedVoucher $unpostedVoucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnpostedVoucher $unpostedVoucher)
    {
        $unpostedVoucher->UnpostedVoucherDetail()->delete();
        $item = $unpostedVoucher->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
