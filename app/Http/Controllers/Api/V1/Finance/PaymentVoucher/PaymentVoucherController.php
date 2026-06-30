<?php

namespace App\Http\Controllers\Api\V1\Finance\PaymentVoucher;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\PaymentVoucher\PaymentVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PaymentVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['list'] = PaymentVoucher::with(['VoucherType','PaymentMode','Currency', 'GeneratedBy', 'CheckedBy', 'AuthorizedBy', 'created_by', 'updated_by','VoucherDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'voucher_no' => 'required',
            'voucher_type' => 'required',
            'voucher_date' => 'required',
            'payment_mode' => 'required',
            'payment_to' => 'required',
            'location' => 'required',
            'currency' => 'required',
            'payment_is' => 'required',
            'cheque_no' => 'required',
            'cheque_name' => 'required',
            'generated_by' => 'required',
            'checked_by' => 'required',
            'authorized_by' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = PaymentVoucher::query()->create($this->input);
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
    public function show(PaymentVoucher $paymentVoucher): JsonResponse
    {
        $data['paymentVoucher'] = $paymentVoucher->load(['VoucherType','PaymentMode','Currency', 'GeneratedBy', 'CheckedBy', 'AuthorizedBy', 'created_by', 'updated_by','VoucherDetail']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentVoucher $paymentVoucher)
    {
        $request->validate([
            'voucher_no' => 'required',
            'voucher_date' => 'required',
            'payment_mode' => 'required',
            'payment_to' => 'required',
            'location' => 'required',
            'currency' => 'required',
            'payment_is' => 'required',
            'cheque_no' => 'required',
            'cheque_name' => 'required',
            'generated_by' => 'required',
            'checked_by' => 'required',
            'authorized_by' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $paymentVoucher->update($this->input);
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
    public function destroy(PaymentVoucher $paymentVoucher): JsonResponse
    {
        $paymentVoucher->VoucherDetail()->delete();
        $item = $paymentVoucher->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
