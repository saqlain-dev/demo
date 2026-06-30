<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\Finance\Budget\AnnualBudgetDetail;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectBudgetDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['list'] = ProjectBudgetDetail::with(['ProjectBudgetId.ProjectId','HeadId','created_by', 'updated_by','activity'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_budget_id' => 'required|integer|exists:project_budgets,id',
            'budget_category' => 'required',
            'head_id' => 'required',
            'unit_type' => 'required|integer',
            'unit' => 'required|integer',
            'number' => 'required',
            'amount' => 'required',
            'rate' => 'required',
            //'requested_funds' => 'required',
            //'cost_shared_applicants' => 'required',
            'program_total' => 'required',
            'sub_total' => 'required',
            'grand_total' => 'required',
            'activity_id' => 'nullable|integer|exists:activities,id',
        ]);
        try {
            DB::beginTransaction();
            $item = ProjectBudgetDetail::query()->create($this->input);
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
    public function show(ProjectBudgetDetail $projectBudgetDetail): JsonResponse
    {
        $data['projectBudgetDetail'] = $projectBudgetDetail->load(['ProjectBudgetId.ProjectId','HeadId','created_by', 'updated_by','activity']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectBudgetDetail $projectBudgetDetail)
    {
        $request->validate([
            'project_budget_id' => 'required|integer|exists:project_budgets,id',
            'budget_category' => 'required',
            'head_id' => 'required',
            'unit' => 'required|integer',
            'unit_type' => 'required|integer',
            'number' => 'required',
            'amount' => 'required',
            'rate' => 'required',
            //'requested_funds' => 'required',
            //'cost_shared_applicants' => 'required',
            'program_total' => 'required',
            'sub_total' => 'required',
            'grand_total' => 'required',
            'activity_id' => 'nullable|integer|exists:activities,id',
        ]);
        try {
            DB::beginTransaction();
            $item = $projectBudgetDetail->update($this->input);
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
    public function destroy(ProjectBudgetDetail $projectBudgetDetail): JsonResponse
    {
        $item = $projectBudgetDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
