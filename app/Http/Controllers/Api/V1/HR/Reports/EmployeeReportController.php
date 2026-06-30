<?php

namespace App\Http\Controllers\Api\V1\HR\Reports;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class EmployeeReportController extends Controller
{
    public function activeEmployeeReport(Request $request)
    {
        $query = Employee::query()->where('employee_type', 13);

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['activeEmployees'] = $queryFilterDate->with($this->getRelations())->get();

        return $data;
    }

    public function newEmployeeReport(Request $request)
    {
        $query = Employee::query()->where('employee_type', 15)->whereNull('leave_date');

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['newEmployees'] = $queryFilterDate->with($this->getRelations())->get();

        return $data;
    }

    public function resignedEmployeeReport(Request $request)
    {
        $query = Employee::query()->where('employee_type', 16)->whereNull('leave_date');

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['resignedEmployees'] = $queryFilterDate->with($this->getRelations())->get();
        return $data;
    }
    public function districtEmployeeReport(Request $request)
    {


        $query = Employee::select('employees.*','districts.name as district_name')
            ->join('districts', 'employees.district_id', '=', 'districts.id');

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['districtEmployees'] = $queryFilterDate->with($this->getRelations())->get();

        $data['districtGenderCount'] = $data['districtEmployees']->groupBy('district_name')->map(function ($districtGroup) {
            return [
                'male_count' => $districtGroup->where('gender_id', 34)->count(),
                'female_count' => $districtGroup->where('gender_id', 33)->count(),
            ];
        })->values()->toArray();
        return $data;
    }
    public function departmentEmployeeReport(Request $request)
    {


        $query = Employee::select('employees.*','type_values.name as department_name')
            ->join('type_values', 'employees.department_id', '=', 'type_values.id');

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['departmentEmployees'] = $queryFilterDate->with($this->getRelations())->get();
        return $data;
    }
    public function genderCountEmployeeReport(Request $request)
    {
        $query = Employee::select('type_values.name as gender_name', \DB::raw('COUNT(*) as count'))
            ->join('type_values', 'employees.gender_id', '=', 'type_values.id');

        $query = $this->filterEmployee($query, $request);
        $queryFilterDate = $this->filterDate($query, $request);

        $data['genderWiseCount'] = $queryFilterDate
            ->groupBy('type_values.name')
            ->get();

        return $data;
    }

    public function employeeDetailReport(Request $request)
    {
        $query = Employee::query();

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['employeeDetails'] = $queryFilterDate->with($this->getRelations())->get();

        return $data;
    }
    public function exitEmployeeReport(Request $request)
    {
        $query = Employee::with('exitEmployeeDetail')
        ->where('employee_type', 366)
        ->whereHas('exitEmployeeDetail', function($query) use ($request) {
            if ($request->input('from_date') && $request->input('to_date')) {
                $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
            } elseif ($request->input('from_date')) {
                $query->whereDate('created_at', '>=', $request->input('from_date'));
            } elseif ($request->input('to_date')) {
                $query->whereDate('created_at', '<=', $request->input('to_date'));
            }
        });

        $query = $this->filterEmployee($query, $request);

        $data['exitEmployee'] = $query->with($this->getRelations())->get();

        return $data;
    }

    private function filterEmployee($query, $request)
    {

        if($request->input('head_office')){
            $query->where('head_office_id', $request->head_office);
        }

        if($request->input('branch_office_id')){
            $query->where('branch_office_id', $request->branch_office_id);
        }

        if($request->input('department_id')){
            $query->where('department_id', $request->department_id);
        }

        if($request->input('employee_type')){
            $query->where('employee_type', $request->employee_type);
        }

        if($request->input('religion_id')){
            $query->where('religion_id', $request->religion_id);
        }
        if($request->input('designation_id')){
            $query->where('designation_id', $request->designation_id);
        }
        if($request->input('district_id')){
            $query->where('district_id', $request->district_id);
        }


        return $query;
    }

    private function filterDate($query, $request)
    {
        if ($request->input('from_date') && $request->input('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->input('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->input('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return $query;
    }

    private function getRelations()
    {
        return [
            'shift',
            'EmployeeSalary',
            'marital',
            'employeeTyp',
            'department',
            'bloodGroupName',
            'parentage',
            'religion',
            'gender',
            'referenceName',
            'user',
            'reportTo',
            'district',
            'headOffice',
            'branchOffice',
            'designation',
            'report',
            'qualification',
            'experience',
            'salarySetup',
            'employeeAllowanceDeduction',
            'employeeChnageStatus',
            'grade',
            'payScale'
        ];
    }
}
