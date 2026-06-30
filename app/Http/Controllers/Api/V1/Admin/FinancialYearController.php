<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\FinancialYear;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FinancialYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-hr',
        ]);

        $data['data'] = FinancialYear::query()->with('financialYear')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-hr',
        ]);

        $request->validate([
            'financial_year' => 'required|integer|unique:financial_years,financial_year',
            'start_date' => 'required|date|before_or_equal:end_date|date_format:Y-m-d',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
        ]);


        try {
            DB::beginTransaction();

            $financialYear = FinancialYear::query()->create($request->all());

            if($financialYear->status == 1){
                FinancialYear::query()->whereNotIn('id',[$financialYear->id])->update(array('status'=>0));
            }

            DB::commit();
            return resp(1, 'Successful!', $financialYear, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FinancialYear $financialYear)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-hr',
        ]);

        $data['data'] = $financialYear->load('financialYear');
        return resp(1, 'Successful!', $data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FinancialYear $financialYear)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-hr',
        ]);

        /*$request->validate([
            'financial_year' => 'required|integer',
            'start_date' => 'required|date|before_or_equal:end_date|date_format:Y-m-d',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
        ]);*/
        $request->validate([
            'financial_year' => [
                'required',
                'integer',
                Rule::unique('financial_years', 'financial_year')->ignore($financialYear->id),
            ],
            'start_date' => 'required|date|before_or_equal:end_date|date_format:Y-m-d',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
        ]);
        try {
            DB::beginTransaction();

            $financialYear->update($request->all());
            if($financialYear->status == 1){
                FinancialYear::query()->whereNotIn('id',[$financialYear->id])->update(array('status'=>0));
            }

            DB::commit();
            return resp(1, 'Successful!', $financialYear, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialYear $financialYear)
    {
        $this->authorizeAny([
            'manage_finance_configuration',
            'configuration-hr',
        ]);

        $financialYear->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['financial_years_list'] = Type::getTypeValues('financial-years');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
