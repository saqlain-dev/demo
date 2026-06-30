<?php

namespace App\Http\Controllers\Api\V1\Finance\Voucher;

use App\Http\Controllers\Controller;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\Finance\Voucher\UnpostedVoucher;
use App\Models\Finance\Voucher\UnpostedVoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnpostedVoucherDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function show(UnpostedVoucherDetail $unpostedVoucherDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnpostedVoucherDetail $unpostedVoucherDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnpostedVoucherDetail $unpostedVoucherDetail)
    {
        //
    }
}
