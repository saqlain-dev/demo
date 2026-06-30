<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcessName;
use App\Models\Configuration\GeneralTemplates;
use App\Models\Employee;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GeneralTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('letter_setup_view');

        $data['items'] = GeneralTemplates::with(['TemplateType','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('letter_setup_create');

        $request->validate([
            'template_name' => 'required',
            'template_data' => 'required',
            'template_key' => 'required',
            'template_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data['item'] = GeneralTemplates::query()->create($this->input);
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
    public function show(GeneralTemplates $generalTemplates): JsonResponse
    {
        $this->authorize('letter_setup_view');

        $data['item'] = $generalTemplates->load(['TemplateType','created_by','updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('letter_setup_update');

        $generalTemplates = GeneralTemplates::query()->findOrFail($id);
        $request->validate([
            'template_name' => 'required',
            'template_data' => 'required',
            'template_key' => 'required',
            'template_type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $data['item'] = $generalTemplates->update($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public static function getConfigurationDropdown()
    {
        $data['template_type']=Type::getTypeValues('template-type');
        $data['general_templates'] = GeneralTemplates::query()->where('status',1)->get();
        $data['employees']= Employee::with(['EmployeeSalary'])->get();
        $data['approval_process_list']= ApprovalProcessName::all();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GeneralTemplates $generalTemplates): JsonResponse
    {
        $this->authorize('letter_setup_delete');

        $data['item'] = $generalTemplates->delete();
        return resp('1', 'Record Deleted Successfully!', $data, Response::HTTP_OK);
    }
}
