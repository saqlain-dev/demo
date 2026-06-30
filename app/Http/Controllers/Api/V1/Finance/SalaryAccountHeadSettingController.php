<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\SalaryAccountHeadSetting;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalaryAccountHeadSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'account_head_id' => 'required|integer',
                'allowance_deduction_id' => 'required|integer',
            ]);

            $salaryAccountHead=SalaryAccountHeadSetting::query()->create($this->input);
            DB::commit();
           $data['salaryAccountHead']=$salaryAccountHead->load('accountHead','allowance_deduction');
            return resp('1', 'Salary account head added Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalaryAccountHeadSetting $salaryAccountHeadSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalaryAccountHeadSetting $salary_account_head)
    {

        try {
            DB::beginTransaction();

            $request->validate([
                'account_head_id' => 'required|integer',
                'allowance_deduction_id' => 'required|integer',
            ]);

            SalaryAccountHeadSetting::query()->where('id',$salary_account_head->id)->update($this->input);
            DB::commit();
            $salary_account_head->refresh();
            $data['salaryAccountHead']=$salary_account_head->load('accountHead','allowance_deduction');
            return resp('1', 'Salary account head added Successfully!', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalaryAccountHeadSetting $salaryAccountHeadSetting)
    {
        //
    }

    public function salaryAccountHeadDropdown()
    {
        $allowance_deduction=AllowanceDeduction::all();
        $tax_Deduction=[];
        $tax_Deduction['Category'] = 2;
        $tax_Deduction['Description'] = "TAX";
        $tax_Deduction['CalculatedBy'] = 2;
        $tax_Deduction['EmployerShareCalculatedBy'] = 2;
        $tax_Deduction['IsActive'] = true;
        $tax_Deduction['IsDelete'] = false;
        $tax_Deduction['IsTaxable'] = false;
        $allowance_deduction[]=$tax_Deduction;
        $data['allowance_deduction']=$allowance_deduction;
        $data['coa_list']=ChartOfAccount::query()->where('approval_status',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
