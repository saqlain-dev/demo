<?php

namespace App\Http\Controllers\Api\V1\HR\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\HR\Appraisal\SectionQuestion;
use App\Models\HR\Appraisal\PerformancePlanning;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SectionQuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data = SectionQuestion::query()->with(['Kpis.DesignationId'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
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
            //'designation_id' => 'required|integer|exists:designations,id',
            'type_value_id' => 'required',
            'question' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $item = SectionQuestion::query()->create($request->all());

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
    public function show(SectionQuestion $sectionQuestion)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data['item'] = $sectionQuestion->load(['Kpis.designation']);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SectionQuestion $sectionQuestion)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $request->validate([
            //'kpi_id' => 'required|integer|exists:designations,id',
            'type_value_id' => 'required',
            'question' => 'required',
        ]);
        try {
            DB::beginTransaction();

            $parent = $sectionQuestion->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $sectionQuestion, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SectionQuestion $sectionQuestion)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        if ($sectionQuestion->employeeWorkplans()->count() > 0) {
            return resp(0,'Cannot be deleted. Indicator is attached to employee workplan.', [],Response::HTTP_OK);
        }

        $sectionQuestion->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getKpiIndicatorsMappings($indicatorId)
    {
        $data['item'] = SectionQuestion::query()->with(['kpiIndicatorsMappings.designations'])->findOrFail($indicatorId);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

}
