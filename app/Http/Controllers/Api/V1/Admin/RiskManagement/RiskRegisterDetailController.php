<?php

namespace App\Http\Controllers\Api\V1\Admin\RiskManagement;

use App\Http\Controllers\Controller;
use App\Models\Admin\RiskManagement\RiskRegister;
use App\Models\Admin\RiskManagement\RiskRegisterDetail;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RiskRegisterDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $this->authorizeAny([]);
        
        $data['data'] = RiskRegisterDetail::query()->with('financialYear')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $this->authorizeAny([]);
        
        $request->validate([
            'risk_register_id' => 'required|integer:exists:risk_registers,id',
            'employee_id' => 'required|integer:exists:employees,id',
            'description' => 'required',
            'control_procedures' => 'required',
            'risk_closure_reason' => 'required',
            'risk_category_id' => 'required',
            'risk_probability_id' => 'required',
            'risk_impact_id' => 'required',
            'overall_risk_id' => 'required',
            'risk_approach_id' => 'required',
            'risk_status_id' => 'required',
        ]);


        try {
            DB::beginTransaction();

            $riskRegisterDetail = RiskRegisterDetail::query()->create($request->all());

            DB::commit();
            return resp(1, 'Successful!', $riskRegisterDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RiskRegisterDetail $riskRegisterDetail)
    {
        // $this->authorizeAny([]);
        
        $data['data'] = $riskRegisterDetail->load('financialYear');
        return resp(1, 'Successful!', $data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiskRegisterDetail $riskRegisterDetail)
    {
        // $this->authorizeAny([]);

        $request->validate([
            'risk_register_id' => 'required|integer:exists:risk_registers,id',
            'employee_id' => 'required|integer:exists:employees,id',
            'description' => 'required',
            'control_procedures' => 'required',
            'risk_closure_reason' => 'required',
            'risk_category_id' => 'required',
            'risk_probability_id' => 'required',
            'risk_impact_id' => 'required',
            'overall_risk_id' => 'required',
            'risk_approach_id' => 'required',
            'risk_status_id' => 'required',
        ]);
        
        try {
            DB::beginTransaction();

            $riskRegisterDetail->update($request->all());
            
            DB::commit();
            return resp(1, 'Successful!', $riskRegisterDetail, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiskRegisterDetail $riskRegisterDetail)
    {
        // $this->authorizeAny([]);

        $riskRegisterDetail->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

}
