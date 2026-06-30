<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\LogBook;
use App\Models\Admin\Library\BookRequest;
use App\Models\Designation;
use App\Models\HR\Appraisal\AppriasalKpi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AppriasalKpiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'appraisal_view',
        ]);

        $data = AppriasalKpi::query()->with(['DesignationId' ,'created_by','updated_by', 'departmentalObjective','KpiIndicators','domain'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'appraisal_create',
        ]);

        $request->validate([
            'departmental_objective_id' => 'required',
            // 'designation_id' => 'required',
            'kpis' => 'required',
            'domain_id' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = AppriasalKpi::query()->create($this->input);
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
    public function show($appriasalKpiId): JsonResponse
    {
        $this->authorizeAny([
            'appraisal_view',
        ]);

        $appriasalKpi = AppriasalKpi::query()->with(['DesignationId' ,'created_by','updated_by', 'departmentalObjective','KpiIndicators','domain'])->findOrFail($appriasalKpiId);
        return resp('1', 'Successful!', $appriasalKpi, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AppriasalKpi $performanceKpi)
    {
        $this->authorizeAny([
            'appraisal_update',
        ]);

        $request->validate([
            'departmental_objective_id' => 'required',
            // 'designation_id' => 'required',
            'kpis' => 'required',
            'domain_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = $performanceKpi->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $performanceKpi->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppriasalKpi $performanceKpi): JsonResponse
    {
        $this->authorizeAny([
            'appraisal_delete',
        ]);

        if ($performanceKpi->KpiIndicators()->count() > 0) {
            return resp(0,'Cannot be deleted. Kpi has child indicators.', [],Response::HTTP_OK);
        }

        $performanceKpi->KpiIndicators()->delete();
        $item = $performanceKpi->delete();

        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getIndicators($kpiId)
    {
        $appriasalKpi = AppriasalKpi::query()->with(['KpiIndicators'])->findOrFail($kpiId);
        return resp('1', 'Successful!', $appriasalKpi, Response::HTTP_OK);
    }
    public function getKpisByDesignation($designationId)
    {
       // $data['designation'] = Designation::with(['kpiIndicatorsMappings' => ['indicator', 'kpi.domain']])->findOrFail($designationId);
        $data['designation'] = Designation::with([
            'kpiIndicatorsMappings' => function ($q) {
                $q->whereHas('indicator')
                    ->whereHas('kpi');
            },
            'kpiIndicatorsMappings.indicator',
            'kpiIndicatorsMappings.kpi.domain'
        ])->findOrFail($designationId);


        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
