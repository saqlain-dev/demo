<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\SalaryAccountConfiguration;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalaryAccountConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['salary_accounts']=SalaryAccountConfiguration::query()->with('ChartOfAccountCode')->get();
        $data['employee_salaries'] = EmployeeSalarySetup::query()->with('employeeDetail.designation','coa')
            ->whereHas('employeeDetail', function ($query) {
                $query->whereIn('employee_type', [13, 15]); // Filters employeeDetail for type 13 or 15
            })
            ->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryAccountConfiguration $salaryAccountConfiguration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryAccountConfiguration $salaryAccountConfig)
    {


        try {
            DB::beginTransaction();

            $request->validate([
                'chart_of_account_code' => 'required|integer',
            ]);
            $update=array(
                'chart_of_account_code'=>$request->chart_of_account_code
            );

           $updateConfig= SalaryAccountConfiguration::query()->find($salaryAccountConfig->id);
           $updateConfig->update($update);
           $updateConfig = $updateConfig->load('ChartOfAccountCode');
            DB::commit();
            $data['salary_accounts']=$updateConfig;
            return resp('1', 'Salary account settings updated Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryAccountConfiguration $salaryAccountConfiguration)
    {
        //
    }

    public function salaryAccountConfigDropdown()
    {
        $data['coa_list']=ChartOfAccount::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }


}
