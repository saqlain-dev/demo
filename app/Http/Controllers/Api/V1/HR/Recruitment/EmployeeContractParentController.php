<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
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
