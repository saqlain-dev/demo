<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\DepartmentalObjective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DepartmentalObjectiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data = DepartmentalObjective::query()->with(['department', 'created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'department_id' => 'required',
            'objective' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = DepartmentalObjective::query()->create($this->input);
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
    public function show(DepartmentalObjective $departmentalObjective): JsonResponse
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $departmentalObjective = $departmentalObjective->load(['department', 'created_by','updated_by']);
        return resp('1', 'Successful!', $departmentalObjective, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DepartmentalObjective $departmentalObjective)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'department_id' => 'required',
            'objective' => 'required',
        ]);
        
        try {
            DB::beginTransaction();
            $item = $departmentalObjective->update($this->input);
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
    public function destroy(DepartmentalObjective $departmentalObjective): JsonResponse
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $item = $departmentalObjective->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getKpis($objectiveId)
    {
        $data['item'] = DepartmentalObjective::query()->with(['kpis.KpiIndicators'])->findOrFail($objectiveId);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
