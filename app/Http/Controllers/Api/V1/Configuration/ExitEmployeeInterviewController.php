<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\EmployeeOffboarding;
use App\Models\ExitEmployeeInterview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ExitEmployeeInterviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item=ExitEmployeeInterview::query()->with(['EmployeeOffboardingId','created_by','updated_by'])->get();
        return resp(1,'Successful!', $item,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_offboarding_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'name' => 'required',
            'particular' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'EmployeeExitInterveiw');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = ExitEmployeeInterview::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExitEmployeeInterview $exitEmployeeInterview): JsonResponse
    {
        $logBook = $exitEmployeeInterview->load(['EmployeeOffboardingId','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExitEmployeeInterview $exitEmployeeInterview)
    {
        $request->validate([
            'employee_offboarding_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'name' => 'required',
            'particular' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'EmployeeExitInterveiw');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $exitEmployeeInterview->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImages($request,$folder){

        $file = $request->file('attachment');
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
    public function destroy(ExitEmployeeInterview $exitEmployeeInterview): JsonResponse
    {
        $item = $exitEmployeeInterview->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
