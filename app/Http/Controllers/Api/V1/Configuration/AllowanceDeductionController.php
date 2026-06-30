<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceDeductionResource;
use App\Models\Configuration\AllowanceDeduction;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\HR\PreGrossSalaryAllowances;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class AllowanceDeductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data['allowance_listing']=AllowanceDeductionResource::collection(AllowanceDeduction::query()->with('employeeType')->get());
        $data['gross_allowances']=PreGrossSalaryAllowances::query()->with('allowanceType')->get();
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

        try {
            DB::beginTransaction();

            $request->validate([
                'description' => 'required|string',
                'category' => 'required|integer',
                'calculated_by' => 'required|integer',
                //'employee_type' => 'required',
                'value' => 'required',
                'employee_calculated_by' => ($request->category == 2) ? 'required|integer' : '',
                'employee_value' => ($request->category == 2) ? 'required|integer' : '',
                'liter' => ($request->calculated_by == 3) ? 'required' : '',
            ]);

            $AllowanceDeduction=AllowanceDeduction::query()->create($this->input);
            DB::commit();
            return resp('1', 'Allowance / Deduction added Successfully!', $AllowanceDeduction->load('employeeType'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(AllowanceDeduction $allowanceDeduction)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $data['allowanceDetails']=$allowanceDeduction->load('employeeType');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AllowanceDeduction $allowanceDeduction)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        try {
            DB::beginTransaction();

            $request->validate([
                'description' => 'required|string',
                'category' => 'required|integer',
                'calculated_by' => 'required|integer',
                //'employee_type' => 'required',
                'value' => 'required',
                'employee_calculated_by' => ($request->category == 2) ? 'required|integer' : '',
                'employee_value' => ($request->category == 2) ? 'required|integer' : '',
                'liter' => ($request->calculated_by == 3) ? 'required' : '',
            ]);

            AllowanceDeduction::query()->where('id',$allowanceDeduction->id)->update($this->input);
            DB::commit();
            $allowanceDeduction->refresh();
            return resp('1', 'Allowance / Deduction updated Successfully!', $allowanceDeduction->load('employeeType'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AllowanceDeduction $allowanceDeduction)
    {
        $this->authorizeAny([
            'configuration-hr',
        ]);

        $allowanceDeduction->delete();
        return resp(1, 'Record deleted Successfully', [], Response::HTTP_OK);
    }

    public function allowanceDropDown()
    {
        $data['category'] = [
            ['id' => 1, 'name' => 'Allowance'],
            ['id' => 2, 'name' => 'Deduction']
        ];
        $data['calculated_by'] = [
            ['id' => 1, 'name' => 'Percentage'],
            ['id' => 2, 'name' => 'Fixed Amount'],
            ['id' => 3, 'name' => 'Per Liter']
        ];
        $data['employee_type']=Type::getTypeValues('employee-type');
        $data['coa_list']=ChartOfAccount::query()->where('approval_status',1)->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
