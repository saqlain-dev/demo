<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubGrants\SubGrantFinancialReport;
use App\Models\GratuityCalculation;
use App\Models\GratuityCalculationDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GratuityCalculationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = GratuityCalculationDetail::with(['EmployeeId' => ['headOffice','branchOffice','designation','grade'],'created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            '*.gratuity_calculation_id' => 'required',
            '*.employee_id' => 'required',
            '*.gratuity_amount' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $createdItems = [];

            foreach ($request->all() as $detail) {
                $createdItem = GratuityCalculationDetail::query()->create([
                    'gratuity_calculation_id' => $detail['gratuity_calculation_id'],
                    'employee_id' => $detail['employee_id'],
                    'percentage' => $detail['percentage'],
                    'gratuity_amount' => $detail['gratuity_amount'],
                    'sub_total' => $detail['sub_total'],
                    'total_amount' => $detail['total_amount'],
                ]);
                $createdItems[] = $createdItem;
            }

            DB::commit();
            return resp('1', 'Records Created Successfully!', $createdItems, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create records!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(GratuityCalculationDetail $gratuityCalculationDetail): JsonResponse
    {
        $gratuityCalculation = $gratuityCalculationDetail->load(['EmployeeId' => ['headOffice','branchOffice','designation','grade'], 'created_by','updated_by']);
        return resp('1', 'Successful!', $gratuityCalculation, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GratuityCalculationDetail $gratuityCalculationDetail)
    {
        $request->validate([
            '*.id' => 'required|exists:gratuity_calculation_details,id', // Ensure the record exists
            '*.gratuity_calculation_id' => 'required',
            '*.employee_id' => 'required',
            '*.gratuity_amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $updatedItems = [];
            foreach ($request->all() as $detail) {
                $item = GratuityCalculationDetail::findOrFail($detail['id']);
                $item->update([
                    'gratuity_calculation_id' => $detail['gratuity_calculation_id'],
                    'employee_id' => $detail['employee_id'],
                    'gratuity_amount' => $detail['gratuity_amount'],
                    'percentage' => $detail['percentage'],
                    'sub_total' => $detail['sub_total'],
                    'total_amount' => $detail['total_amount'],
                ]);
                $updatedItems[] = $item;
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $updatedItems, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GratuityCalculationDetail $gratuityCalculationDetail): JsonResponse
    {
        $item = $gratuityCalculationDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
