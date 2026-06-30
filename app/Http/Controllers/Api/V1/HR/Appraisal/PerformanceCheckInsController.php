<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\PerformanceCheckIn;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PerformanceCheckInsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['item'] = PerformanceCheckIn::query()->with(['employee' => ['department', 'designation'], 'scheduledCheckIns.employeeWorkplan'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'description' => 'required',
            'average_points' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $item = PerformanceCheckIn::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(PerformanceCheckIn $performanceCheckIn)
    {
        $data['item'] = $performanceCheckIn->load(['employee' => ['department', 'designation'], 'scheduledCheckIns.employeeWorkplan']);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PerformanceCheckIn $performanceCheckIn)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'description' => 'required',
            'average_points' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $performanceCheckIn->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $performanceCheckIn, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PerformanceCheckIn $performanceCheckIn)
    {
        $performanceCheckIn->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
}
