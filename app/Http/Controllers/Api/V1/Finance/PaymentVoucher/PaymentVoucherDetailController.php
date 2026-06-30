<?php

namespace App\Http\Controllers\Api\V1\Finance\PaymentVoucher;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentVoucher\PaymentVoucher;
use App\Models\Finance\PaymentVoucher\PaymentVoucherDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentVoucherDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['list'] = PaymentVoucherDetail::with(['PaymentVoucherId','AccountId','created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_voucher_id' => 'required',
            'account_id' => 'required',
            'detail' => 'required',
            'act_code' => 'required',
            'project_code' => 'required',
            'amount' => 'required',
            //'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = PaymentVoucherDetail::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PaymentVoucherDetail $paymentVoucherDetail): JsonResponse
    {
        $data['paymentVoucherDetail'] = $paymentVoucherDetail->load(['PaymentVoucherId','AccountId','created_by', 'updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentVoucherDetail $paymentVoucherDetail)
    {
        $request->validate([
            'payment_voucher_id' => 'required',
            'account_id' => 'required',
            'detail' => 'required',
            'act_code' => 'required',
            'project_code' => 'required',
            'amount' => 'required',
            //'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $paymentVoucherDetail->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentVoucherDetail $paymentVoucherDetail): JsonResponse
    {
        $item = $paymentVoucherDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
