<?php

namespace App\Http\Controllers\Api\V1\Finance\FinanceBill;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceBill\FinanceBill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class FinanceBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = FinanceBill::with(['created_by','updated_by','BudgetEstimateId.refferenceable','BillDetail.BudgetEstimateDetailId'])->orderByDesc('id')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'budget_estimate_id' => 'required',
            'date' => 'required',
            'address' => 'required',
            'total' => 'required',
            //'terms' => 'required',
            'bill_due_date' => 'required',
            //'head_id' => 'required',
            //'class_id' => 'required',
            'description' => 'required',
            'finance_bill_detail.*' => 'required',
        ]);


        $financeBillDetail = $this->input['finance_bill_detail'];
        unset($this->input['finance_bill_detail']);
        try {
            DB::beginTransaction();
            $item = FinanceBill::query()->create($this->input);

            if ($item){
                $childData = array_map(function ($detail) use ($item) {
                    return [
                        'bill_id' => $item->id, // Assign the parent ID to each child record
                        'budget_estimate_detail_id' => $detail['budget_estimate_detail_id'],
                        'item_detail' => $detail['item_detail'],
                        'item_coa' => $detail['item_coa'],
                        'description' => $detail['description'],
                        'quantity' => $detail['quantity'],
                        'rate' => $detail['rate'],
                        'amount' => $detail['amount'],
                        'total' => $detail['total'],
                    ];
                }, $financeBillDetail);
                $item->BillDetail()->createMany($childData);
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
    public function show(FinanceBill $financeBill): JsonResponse
    {
        $financeBill = $financeBill->load(['created_by','updated_by','BudgetEstimateId.refferenceable','BillDetail.BudgetEstimateDetailId']);
        return resp('1', 'Successful!', $financeBill, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinanceBill $financeBill)
    {
        $request->validate([
            'budget_estimate_id' => 'required',
            'date' => 'required',
            'address' => 'required',
            'amount' => 'required',
            //'terms' => 'required',
            'bill_due_date' => 'required',
            //'head_id' => 'required',
            //'class_id' => 'required',
            'narration' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $financeBill->update($this->input);
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
    public function destroy(FinanceBill $financeBill): JsonResponse
    {
        $financeBill->BillDetail()->delete();
        $item = $financeBill->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
