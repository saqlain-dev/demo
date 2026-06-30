<?php

namespace App\Http\Controllers\Api\V1\HR\Insurance;

use App\Http\Controllers\Controller;
use App\Models\HR\Insurance\EmployeeRelative;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeRelativeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = EmployeeRelative::query()->with(['insurance'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_insurance_id' => 'required|integer|exists:employee_insurances,id',
            'name' => 'required|max:255',
            'age' => 'required|max:255',
            'date_of_birth' => 'required|date',
            'relation_id' => 'required|integer|exists:type_values,id',
            'cnic' => 'max:255|unique:employee_relatives,cnic',
        ]);
        try {
            DB::beginTransaction();

            if($request->hasFile('relationship_proof')) {

                $responce = $this->saveRelationshipProof($request, 'relationship_proof');

                if ($responce) {
                    $this->input['relationship_proof'] = $responce;
                }
            }
            $parent = EmployeeRelative::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $parent, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveRelationshipProof($request,$folder){

        $file = $request->file('relationship_proof');
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
     * Display the specified resource.
     */
    public function show(EmployeeRelative $employeeRelative)
    {
        return resp(1, 'Successful!', $employeeRelative->load('insurance'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeRelative $employeeRelative)
    {
        $request->validate([
            'employee_insurance_id' => 'required|integer|exists:employee_insurances,id',
            'name' => 'required|max:255',
            'age' => 'required|max:255',
            'date_of_birth' => 'required|date',
            'relation_id' => 'required|integer|exists:type_values,id',
            //'cnic' => 'required|max:255',
        ]);
        try {
            DB::beginTransaction();

            if($request->hasFile('relationship_proof')) {

                $responce = $this->saveRelationshipProof($request, 'relationship_proof');

                if ($responce) {
                    $this->input['relationship_proof'] = $responce;
                }
            }
            $parent = $employeeRelative->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $employeeRelative, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeRelative $employeeRelative)
    {
        $employeeRelative->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
}
