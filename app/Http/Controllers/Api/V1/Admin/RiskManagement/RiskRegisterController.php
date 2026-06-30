<?php

namespace App\Http\Controllers\Api\V1\Admin\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\Admin\RiskManagement\RiskRegister;
use App\Models\Employee;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RiskRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $this->authorizeAny([]);

        $data['data'] = RiskRegister::query()->with(['riskRegisterDetails' => ['department','riskCategory','riskProbability','riskImpact','overallRisk','riskApproach','riskStatus','createdBy','riskOwner','riskRegisterQuarterly.riskProbability','riskRegisterQuarterly.riskImpact','riskRegisterQuarterly.overallRisk'], 'financialYear', 'createdBy'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $this->authorizeAny([]);

        $request->validate([
            'financial_year_id' => 'required|integer:exists:financial_years,id',
            'title' => 'required|string|max:255',
        ]);


        try {
            DB::beginTransaction();

            $riskRegister = RiskRegister::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $riskRegister, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RiskRegister $riskRegister)
    {
        // $this->authorizeAny([]);

        $data['data'] = $riskRegister->load(['riskRegisterDetails' => ['department','riskCategory','riskProbability','riskImpact','overallRisk','riskApproach','riskStatus','createdBy','riskOwner','riskRegisterQuarterly.riskProbability','riskRegisterQuarterly.riskImpact','riskRegisterQuarterly.overallRisk'], 'financialYear', 'createdBy']);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiskRegister $riskRegister)
    {
        // $this->authorizeAny([]);

        $request->validate([
            'financial_year_id' => 'required|integer:exists:financial_years,id',
            'title' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $riskRegister->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $riskRegister, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiskRegister $riskRegister)
    {
        // $this->authorizeAny([]);

        $riskRegister->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['financial_years'] = FinancialYear::all();
        $data['risk_category'] = Type::getTypeValues('risk-category');
        $data['risk_probability'] = Type::getTypeValues('risk-probability');
        $data['risk_impact'] = Type::getTypeValues('risk-impact');
        $data['overall_risk'] = Type::getTypeValues('overall-risk');
        $data['risk_approach'] = Type::getTypeValues('risk-approach');
        $data['risk_status'] = Type::getTypeValues('risk-status');
        $data['employees'] = Employee::query()->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        $data['departments'] = Type::getTypeValues('department-names');

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

}
