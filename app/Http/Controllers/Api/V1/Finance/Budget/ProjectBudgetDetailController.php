<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\Finance\Budget\AnnualBudgetDetail;
use App\Models\Finance\Budget\ProjectBudgetDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


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
            'unit' => 'required|numeric',
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

    public function importCsv(Request $request, $id)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation Failed!',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('csv_file');
            $data = array_map('str_getcsv', file($file));

            // Extract the headers and rows
            $headers = array_map('trim', $data[0]);
            $rows = array_slice($data, 1);

            DB::beginTransaction();

            foreach ($rows as $index => $row) {
                $rowData = array_combine($headers, $row);

                // Validate individual row
                $rowValidator = Validator::make($rowData, [
                    'category_id'        => 'nullable|integer',
                    'sub_category_id'    => 'nullable|integer',
                    'unit'               => 'nullable|string|max:255',
                    'number'             => 'nullable|numeric',
                    'amount'             => 'nullable|numeric',
                    'rate'               => 'nullable|numeric',
                    'requested_funds'    => 'nullable|numeric',
                    'program_total'      => 'nullable|numeric',
                    'sub_total'          => 'nullable|numeric',
                    'grand_total'        => 'nullable|numeric',
                    'status'             => 'nullable|integer|in:0,1',
                    'created_by'         => 'nullable|integer|exists:users,id',
                    'updated_by'         => 'nullable|integer|exists:users,id',
                    'head_id'            => 'nullable|integer',
                    'activity_id'        => 'nullable|integer',
                    'budget_for'         => 'nullable|string|max:255',
                    'unit_type'          => 'nullable|string|max:255',
                    'budget_category'    => 'nullable|string|max:255',
                ]);

                if ($rowValidator->fails()) {

                    return response()->json([
                        'status' => 0,
                        'message' => "Validation failed for row {$index}!",
                        'errors' => $rowValidator->errors(),
                    ], 422);
                }

                // Insert validated data
                ProjectBudgetDetail::create([
                    'project_budget_id'  => $id,
                    'category_id'        => $rowData['category_id'] ?? null,
                    'sub_category_id'    => $rowData['sub_category_id'] ?? null,
                    'unit'               => $rowData['unit'] ?? null,
                    'number'             => $rowData['number'] ?? null,
                    'amount'             => $rowData['amount'] ?? null,
                    'rate'               => $rowData['rate'] ?? null,
                    'requested_funds'    => $rowData['requested_funds'] ?? null,
                    'program_total'      => $rowData['program_total'] ?? null,
                    'sub_total'          => $rowData['sub_total'] ?? null,
                    'grand_total'        => $rowData['grand_total'] ?? null,
                    'status'             => $rowData['status'] ?? 1,
                    'created_by'         => $rowData['created_by'] ?? null,
                    'updated_by'         => $rowData['updated_by'] ?? null,
                    'head_id'            => $rowData['head_id'] ?? null,
                    'activity_id'        => ($rowData['activity_id'] != "") ? $rowData['activity_id']: null,
                    'budget_for'         => $rowData['budget_for'] ?? null,
                    'unit_type'          => $rowData['unit_type'] ?? null,
                    'budget_category'    => $rowData['budget_category'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => 1,
                'message' => 'CSV data imported successfully!',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Failed to import CSV data!',
                'error' => $e->getMessage(),
            ], 500);
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
            'unit' => 'required|numeric',
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

    public function updateProcType(Request $request)
    {
        $request->validate([
            'procurement_type' => 'required',
            'budget_detail_id' => 'required',
        ]);

        ProjectBudgetDetail::query()->where('id',$request->budget_detail_id)->update(['procurement_type'=>$request->procurement_type]);

        return resp('1', 'Record updated Successfully!', [], Response::HTTP_OK);
    }
}
