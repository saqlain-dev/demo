<?php

namespace App\Http\Controllers\Api\V1\Finance\FinanceBill;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceBill\FinanceBill;
use App\Models\Finance\FinanceBill\FinanceBillDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FinanceBillDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FinanceBillDetail::with(['created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'bill_id' => 'required',
            'budget_estimate_detail_id' => 'required',
            'item_detail' => 'required',
            'item_coa' => 'required',
            'description' => 'required',
            'quantity' => 'required',
            'rate' => 'required',
            'amount' => 'required',
            'total' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = FinanceBillDetail::query()->create($this->input);
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
    public function show(FinanceBillDetail $financeBillDetail): JsonResponse
    {
        $financeBill = $financeBillDetail->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $financeBill, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinanceBillDetail $financeBillDetail)
    {
        $request->validate([
            'bill_id' => 'required',
            'budget_estimate_detail_id' => 'required',
            'item_detail' => 'required',
            'item_coa' => 'required',
            'description' => 'required',
            'quantity' => 'required',
            'rate' => 'required',
            'amount' => 'required',
            'total' => 'required',

        ]);
        try {
            DB::beginTransaction();
            $item = $financeBillDetail->update($this->input);
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
    public function destroy(FinanceBillDetail $financeBillDetail): JsonResponse
    {
        $item = $financeBillDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
