<?php

namespace App\Http\Controllers\Api\V1\Admin\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Admin\RiskManagement\RiskQuarterlyAssessment;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RiskQuarterlyAssessmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $this->authorizeAny([]);

        $data['data'] = RiskQuarterlyAssessment::query()->with(['createdBy','riskProbability','riskImpact','overallRisk'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $this->authorizeAny([]);

        $request->validate([
            'risk_register_detail_id' => 'required|integer:exists:risk_register_details,id',
            'action_taken' => 'required|string',
            'quarter_id' => 'required|integer',
            'risk_probability_id' => 'required|integer',
            'risk_impact_id' => 'required|integer',
            'overall_risk_id' => 'required|integer',
        ]);


        try {
            DB::beginTransaction();

            $riskQuarterlyAssessment = RiskQuarterlyAssessment::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $riskQuarterlyAssessment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RiskQuarterlyAssessment $riskQuarterlyAssessment)
    {
        // $this->authorizeAny([]);

        $data['data'] = $riskQuarterlyAssessment->load(['createdBy','riskProbability','riskImpact','overallRisk']);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiskQuarterlyAssessment $riskQuarterlyAssessment)
    {
        // $this->authorizeAny([]);

        $request->validate([
            'risk_register_detail_id' => 'required|integer:exists:risk_register_details,id',
            'action_taken' => 'required|string',
            'quarter_id' => 'required|integer',
            'risk_probability_id' => 'required|integer',
            'risk_impact_id' => 'required|integer',
            'overall_risk_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $riskQuarterlyAssessment->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $riskQuarterlyAssessment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiskQuarterlyAssessment $riskQuarterlyAssessment)
    {
        // $this->authorizeAny([]);

        $riskQuarterlyAssessment->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

}
