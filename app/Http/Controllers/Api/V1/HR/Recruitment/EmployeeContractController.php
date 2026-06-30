<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\EmployeeContract;
use App\Models\HR\Recruitment\ManageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeContractController extends Controller
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

        $data['items'] = EmployeeContract::with(['created_by','updated_by'])->get();
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
            'parent_employee_contract_id' => 'required',
            'contract_text' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'contract_date' => 'required',
            'contract_type_id' => 'required',
            'is_active' => 'required',
            'hr_attachment' => 'required|file|mimes:pdf|max:5120',
        ]);

        if($request->hasFile('hr_attachment')) {
            $file = $request->file('hr_attachment');
            $responce = $this->saveAttachment($file, 'hr_attachment');
            if ($responce) {
                $this->input['hr_attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            EmployeeContract::query()->where('parent_employee_contract_id',$request->parent_employee_contract_id)->update(array('is_active'=>0));
            $data['item'] = EmployeeContract::query()->create($this->input);
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
    public function show(EmployeeContract $employeeContract): JsonResponse
    {
        $this->authorizeAny([
            'employee_contract_view',
            'consultant_contract_view',
            'manage_employee_portal',
            'manage_audit_employee_management',
            'manage_audit_consultant_management',
        ]);

        $data['item'] = $employeeContract->load(['created_by','updated_by', 'parentEmployeeContract.employee' => ['designation','department', 'employeeTyp']]);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
    public function getEmployeeContract(string $empId): JsonResponse
    {
        $this->authorizeAny([
            'manage_employee_portal',
        ]);

        $data['item'] =  EmployeeContract::query()->with(['EmployeeId','created_by','updated_by'])->where('employee_id', $empId)->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeContract $employeeContract)
    {
        $this->authorizeAny([
            'employee_contract_update',
            'consultant_contract_update',
        ]);

        $request->validate([
            'parent_employee_contract_id' => 'required',
            'contract_text' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'contract_date' => 'required',
            'contract_type_id' => 'required',
            'is_active' => 'required',
            //'hr_attachment' => 'required|file|mimes:pdf|max:5120', // PDF file less than 5 MB
            //'employee_attachment' => 'file|mimes:pdf|max:5120', // PDF file less than 5 MB
        ]);
        if($request->hasFile('hr_attachment')) {
            $file = $request->file('hr_attachment');
            $responce = $this->saveAttachment($file, 'hr_attachment');
            if ($responce) {
                $this->input['hr_attachment'] = $responce;
            }
        }
        if($request->hasFile('employee_attachment')) {
            $file = $request->file('employee_attachment');
            $responce = $this->saveAttachment($file, 'employee_attachment');
            if ($responce) {
                $this->input['employee_attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $data['item'] = $employeeContract->update($this->input);
            DB::commit();
            return resp('1', 'Record updated Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachment($file,$folder, ){


        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeContract $employeeContract): JsonResponse
    {
        $this->authorizeAny([
            'employee_contract_delete',
            'consultant_contract_delete',
        ]);

        $data['item'] = $employeeContract->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }
}
