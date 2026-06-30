<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\HR\Recruitment\EmployeeContract;
use App\Models\HR\Recruitment\ParentEmployeeContract;
use App\Models\HR\Recruitment\ManageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeContractParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'employee_contract_view',
            'consultant_contract_view',
            'manage_audit_employee_management',
            'manage_audit_consultant_management',
        ]);

        $data['items'] = ParentEmployeeContract::with(['employee' => ['designation','department', 'employeeTyp'],'created_by','updated_by', 'employeeContractDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function employeeContractReport(Request $request)
    {
        $query = ParentEmployeeContract::with(['employee','employeeContractDetail']);

        $query = $this->filterEmployee($query, $request);

        $queryFilterDate = $this->filterDate($query, $request);

        $data['activeEmployees'] = $queryFilterDate->with([
            'employee' => function ($query) {
                $query->with($this->getRelations());
            },
            'employeeContractDetail'
        ])->get();

        return $data;
    }
    private function filterEmployee($query, $request)
    {
        $query->whereHas('employee', function ($q) use ($request) {

            if ($request->input('head_office')) {
                $q->where('head_office_id', $request->head_office);
            }

            if ($request->input('branch_office_id')) {
                $q->where('branch_office_id', $request->branch_office_id);
            }

            if ($request->input('department_id')) {
                $q->where('department_id', $request->department_id);
            }

            if ($request->input('employee_type')) {
                $q->where('employee_type', $request->employee_type);
            }

            if ($request->input('religion_id')) {
                $q->where('religion_id', $request->religion_id);
            }

            if ($request->input('designation_id')) {
                $q->where('designation_id', $request->designation_id);
            }

            if ($request->input('district_id')) {
                $q->where('district_id', $request->district_id);
            }

        });

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

    private function filterDate($query, $request)
    {
        if ($request->input('from_date') || $request->input('to_date')) {
            $query->whereHas('employeeContractDetail', function ($q) use ($request) {
                if ($request->input('from_date') && $request->input('to_date')) {
                    $q->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
                } elseif ($request->input('from_date')) {
                    $q->whereDate('created_at', '>=', $request->input('from_date'));
                } elseif ($request->input('to_date')) {
                    $q->whereDate('created_at', '<=', $request->input('to_date'));
                }
            });
        }

        return $query;
    }

    /*private function filterDate($query, $request)
    {
        if ($request->input('from_date') && $request->input('to_date')) {
            $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
        } elseif ($request->input('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        } elseif ($request->input('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        return $query;
    }*/

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'employee_contract_create',
            'consultant_contract_create',
        ]);

        $request->validate([
            'employee_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $data['item'] = ParentEmployeeContract::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ParentEmployeeContract $employeeContractParent): JsonResponse
    {
        $this->authorizeAny([
            'employee_contract_view',
            'consultant_contract_view',
            'manage_employee_portal',
            'manage_audit_employee_management',
            'manage_audit_consultant_management',
        ]);

        $data['item'] = $employeeContractParent->load(['employee' => ['designation','department', 'employeeTyp'],'created_by','updated_by', 'employeeContractDetail.contractType']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    //show by employee id
    public function showByEmployee($emp_id): JsonResponse
    {
        $employeeContractParent = ParentEmployeeContract::where('employee_id', $emp_id)->first();

        if (!$employeeContractParent) {
            return resp(1, 'Record not found!', [], Response::HTTP_NOT_FOUND);
        }

        $data['item'] = $employeeContractParent->load([
            'employee' => ['designation', 'department', 'employeeTyp'],
            'created_by',
            'updated_by',
            'employeeContractDetail.contractType'
        ]);

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ParentEmployeeContract $employeeContractParent)
    {
        $this->authorizeAny([
            'employee_contract_update',
            'consultant_contract_update',
        ]);

        $request->validate([
            'employee_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $data['item'] = $employeeContractParent->update($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ParentEmployeeContract $employeeContractParent): JsonResponse
    {
        $this->authorizeAny([
            'employee_contract_delete',
            'consultant_contract_delete',
        ]);

        $data['item'] = $employeeContractParent->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }
}
