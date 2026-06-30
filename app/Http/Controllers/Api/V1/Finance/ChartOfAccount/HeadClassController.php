<?php

namespace App\Http\Controllers\Api\V1\Finance\ChartOfAccount;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\ChartOfAccountClass;
use App\Models\Finance\ChartOfAccount\HeadClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HeadClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $data['list'] = HeadClass::with(['ProjectId','created_by', 'updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'project_id' => 'required',
            'chart_of_account_id' => 'nullable|integer', // Validate the chart_of_account_id if present
        ]);
        try {
            DB::beginTransaction();
            $item = HeadClass::query()->create($this->input);
            if ($item) {
                // Check for chart_of_account_id in the request
                if ($request->has('chart_of_account_id')) {
                    // Prepare input for ChartOfAccountClass
                    $chartOfAccountData = [
                        'head_class_id' => $item->id,
                        'chart_of_account_id' => $request->chart_of_account_id,
                        // Include any other necessary fields for ChartOfAccountClass
                    ];

                    // Create a new ChartOfAccountClass entry
                    $newItem = ChartOfAccountClass::query()->create($chartOfAccountData);
                }
            }
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
    public function show(HeadClass $headClass): JsonResponse
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $data['headClass'] = $headClass->load(['ProjectId','created_by', 'updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HeadClass $headClass)
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $headClass->update($this->input);
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
    public function destroy(HeadClass $headClass): JsonResponse
    {
        $this->authorizeAny([
            'manage_finance_configuration'
        ]);

        $item = $headClass->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
