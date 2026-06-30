<?php

namespace App\Http\Controllers\Api\V1\HR\Payroll;

use App\Http\Controllers\Controller;
use App\Models\HR\Payroll\PayrollTaxRates;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PayrollTaxRatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'payroll_tax_rates_view',
            'manage_audit_tax_management',
        ]);

        $data['payroll_tax_rate']=PayrollTaxRates::query()->with('fiscalYear')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'payroll_tax_rates_create',
        ]);

        $request->validate([
            'financial_year_id' => 'required',
            'financial_year' => 'required',
            'salary_from' => 'required',
            'salary_to' => 'required',
            'fixed_amount' => 'required',
            'tax_rate' => 'required',
            'minimum_tax_amount' => 'required',
        ]);

        try {

            DB::beginTransaction();
            $fiscalYear = $request->financial_year;

            list($startYear, $endYear) = explode('-', $fiscalYear);

            $this->input['financial_year_start_date'] = date('Y-m-d', strtotime("$startYear-07-01"));
            $this->input['financial_year_end_date']= date('Y-m-d H:i:s', strtotime("$endYear-06-30 23:59:59.000"));

            $taxRate=PayrollTaxRates::query()->create($this->input);
            $data['taxRate']=$taxRate->load('fiscalYear');
            DB::commit();
            return resp('1', 'Payroll tax rate added Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PayrollTaxRates $payrollTaxRate)
    {
        $this->authorizeAny([
            'payroll_tax_rates_view',
            'manage_audit_tax_management',
        ]);

        $data['payroll_tax_rate']=$payrollTaxRate->load('fiscalYear');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PayrollTaxRates $payrollTaxRate)
    {
        $this->authorizeAny([
            'payroll_tax_rates_update',
        ]);

        $request->validate([
            'financial_year_id' => 'required',
            'financial_year' => 'required',
            'salary_from' => 'required',
            'salary_to' => 'required',
            'fixed_amount' => 'required',
            'tax_rate' => 'required',
            'minimum_tax_amount' => 'required',
        ]);

        try {

            DB::beginTransaction();
            $fiscalYear = $request->financial_year;

            list($startYear, $endYear) = explode('-', $fiscalYear);

            $this->input['financial_year_start_date'] = date('Y-m-d', strtotime("$startYear-07-01"));
            $this->input['financial_year_end_date']= date('Y-m-d H:i:s', strtotime("$endYear-06-30 23:59:59.000"));

            PayrollTaxRates::query()->where('id',$payrollTaxRate->id)->update($this->input);
            $payrollTaxRate=$payrollTaxRate->refresh();
            $data['taxRate']=$payrollTaxRate->load('fiscalYear');
            DB::commit();
            return resp('1', 'Payroll tax rate updated Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PayrollTaxRates $payrollTaxRate)
    {
        $this->authorizeAny([
            'payroll_tax_rates_delete',
        ]);

        $payrollTaxRate->delete();
        return resp(1, 'Payroll tax rate deleted Successfully!', [], Response::HTTP_OK);
    }
    public function payrollTaxRateDropDown()
    {
        $data['financial_years']=Type::getTypeValues('financial-years');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
    public function payrollTaxRateByFiscalYear(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required',
        ]);
        $data['payroll_tax_rate']=PayrollTaxRates::query()->where('financial_year_id',$request->financial_year_id)->with('fiscalYear')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
