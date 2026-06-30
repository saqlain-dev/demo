<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\Library\BookRequest;
use App\Models\HR\Appraisal\KpiIndicatorsMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class KpiIndicatorsMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data = KpiIndicatorsMapping::query()->with(['designations', 'created_by', 'updated_by', 'kpi', 'indicator'])->get();
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
            'kpi_id' => 'required|exists:appriasal_kpis,id',
            'indicator_id' => 'required|exists:section_questions,id',
            'designations' => 'required|array',
            'designations.*' => 'exists:designations,id',
        ]);

        try {
            DB::beginTransaction();

            $item = KpiIndicatorsMapping::query()->create($request->except('designations'));
            $item->designations()->sync($request->designations);

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
    public function show($kpiIndicatorMappingId): JsonResponse
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $kpiIndicatorMapping = KpiIndicatorsMapping::query()->with(['designations', 'created_by', 'updated_by', 'kpi', 'indicator'])->findOrFail($kpiIndicatorMappingId);
        
        return resp('1', 'Successful!', $kpiIndicatorMapping, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KpiIndicatorsMapping $kpiIndicatorsMapping)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            'kpi_id' => 'required|exists:appriasal_kpis,id',
            'indicator_id' => 'required|exists:section_questions,id',
            'designations' => 'required|array',
            'designations.*' => 'exists:designations,id',
        ]);

        try {
            DB::beginTransaction();

            $item = $kpiIndicatorsMapping->update($request->except('designations'));
            $kpiIndicatorsMapping->designations()->sync($request->designations);

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
    public function destroy(KpiIndicatorsMapping $kpiIndicatorsMapping): JsonResponse
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $kpiIndicatorsMapping->designations()->delete();
        $item = $kpiIndicatorsMapping->delete();

        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
