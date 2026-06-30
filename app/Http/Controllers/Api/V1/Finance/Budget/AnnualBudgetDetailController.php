<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\Finance\Budget\AnnualBudget;
use App\Models\Finance\Budget\AnnualBudgetDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AnnualBudgetDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['list'] = AnnualBudgetDetail::with(['AnnualBudgetId','ProjectId','HeadId.AccountTypeId','created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'annual_budget_id' => 'required',
            //'project_id' => 'required',
            'head_id' => 'required',
            //'sub_category_id' => 'required',
            //'item_detail' => 'required',
            'budget_amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = AnnualBudgetDetail::query()->create($this->input);
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
    public function show(AnnualBudgetDetail $annualBudgetDetail): JsonResponse
    {
        $data['annualBudgetDetail'] = $annualBudgetDetail->load(['AnnualBudgetId.projectBudgets.BudgetDetail.Head','ProjectId','HeadId','created_by', 'updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $annualBudgetDetail = AnnualBudgetDetail::query()->findOrFail($id);
        $request->validate([
            'annual_budget_id' => 'required',
            //'project_id' => 'required',
            'head_id' => 'required',
            //'item_detail' => 'required',
            'budget_amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $annualBudgetDetail->update($this->input);
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
    public function destroy(AnnualBudgetDetail $annualBudgetDetail): JsonResponse
    {
        $item = $annualBudgetDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
