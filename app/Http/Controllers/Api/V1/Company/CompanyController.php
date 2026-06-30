<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\ErpActivity\ErpActivity;
use App\Models\SalesTeam\SalesTeam;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['companies']=Company::query()->with('currency','country')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required',
            'country_id' => 'required',

        ]);

        try {
            DB::beginTransaction();

            $company=Company::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $company->load('currency','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        return resp(1, 'Successful!', $company->load('currency','country'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'company_name' => 'required',
            'country_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $company->update($this->input);
            $company->refresh();
            DB::commit();
            return resp(1, 'Successfully Update!', $company->load('currency','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getCompanyDropdowns()
    {
        $data['currency']=Type::getTypeValues('currency');
        $data['countries']=DB::table('countries')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    function getEmployeeHierarchy($employee_id) {
        // Fetch the main employee details along with sales team details
        $employee = Employee::with('salesTeamEmployee')->find($employee_id);

        if (!$employee) {
            return null; // Return null if employee not found
        }

        // Fetch subordinates and recursively build their hierarchy
        $employee->subordinates = Employee::where('report_to_id', $employee_id)
            ->get()
            ->map(function ($subordinate) {
                $subordinate->subordinates = $this->getEmployeeHierarchy($subordinate->id);
                return $subordinate;
            });

        return $employee;
    }
    function getAllEmployeesAtSameLevel($employee, &$result = []) {
        if (!$employee || !is_object($employee)) {
            return;
        }

        // Store the current employee in result
        $result[] = $employee;

        // Check if subordinates exist before looping
        if (!empty($employee->subordinates)) {
            foreach ($employee->subordinates as $subordinate) {
               $this->getAllEmployeesAtSameLevel($subordinate, $result);
            }
        }

        return $result;
    }




    public function getCRMDropdowns()
    {
        $data['currency']=Type::getTypeValues('currency');
        $data['countries']=DB::table('countries')->get();
        $data['market_segment']=Type::getTypeValues('market-segment');
        $data['customer_group']=Type::getTypeValues('customer-group');
        $data['industry']=Type::getTypeValues('industry-name');
        $data['company_listing']=Company::query()->with('currency','country')->get();
        $data['customer_type']=Type::getTypeValues('customer-type');
        $data['salutation']=Type::getTypeValues('salutation');
        $data['gender']=Type::getTypeValues('gender');
        $data['lead_status']=Type::getTypeValues('lead-status');
        $data['lead_type']=Type::getTypeValues('lead-type');
        $data['qualification_status']=Type::getTypeValues('qualification-status');
        $data['lead_request_type']=Type::getTypeValues('lead-request-type');
        $data['campaign_type']=Type::getTypeValues('campaign-type');
        $data['campaign_status']=Type::getTypeValues('campaign-status');
        $data['qualification_type']=Type::getTypeValues('qual.ification-type');
        $data['task_status']=Type::getTypeValues('task-status');
        $data['task_priority']=Type::getTypeValues('task-priority');
        $data['lead_owner']=Employee::query()->whereHas('salesTeamEmployee')->with('salesTeamEmployee')->get();
        //$data['employee_hierarchy']=Employee::query()->where('report_to_id',auth()->user()->employee_id)->get();
        $employeeHierarchy=$this->getEmployeeHierarchy(auth()->user()->employee_id);
        $allSameLevelEmployees = $this->getAllEmployeesAtSameLevel($employeeHierarchy);
        $data['employee_hierarchy']=$allSameLevelEmployees;

        $employeeIds=getReportToEmployees(auth()->user()->employee_id);
        $userIds=User::query()->whereIn('employee_id',$employeeIds ?? [])->pluck('id');
        $data['activity_listing']=ErpActivity::query()->whereIn('created_by',$userIds ?? [])->orWhereIn('performed_by',$employeeIds ?? [])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
