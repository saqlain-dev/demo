<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\DevelopmentGoal;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Appraisal\SectionQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DevelopmentGoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['item'] = DevelopmentGoal::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'performance_planning_id' => 'required|integer|exists:performance_plannings,id',
            'goal' => 'required',
            'priority' => 'required|string|max:255',
            'target_date' => 'required|date|date_format:Y-m-d',
        ]);
        try {
            DB::beginTransaction();

            $item = DevelopmentGoal::query()->create($request->all());

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
    public function show(DevelopmentGoal $developmentGoal)
    {
        $data['item'] = $developmentGoal;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DevelopmentGoal $developmentGoal)
    {
        $request->validate([
            'performance_planning_id' => 'required|integer|exists:performance_plannings,id',
            'goal' => 'required',
            'priority' => 'required|string|max:255',
            'target_date' => 'required|date|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();

            $parent = $developmentGoal->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $developmentGoal, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DevelopmentGoal $developmentGoal)
    {
        $developmentGoal->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
}
