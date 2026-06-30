<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Configuration\ClearanceExpCertificate;
use App\Models\EmployeeOffboarding;
use App\Models\ExitEmployeeInterview;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ClearanceExpCertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['certificate'] = ClearanceExpCertificate::query()->with(['EmployeeOffboardingId','certificateType','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_offboarding_id' => 'required',
            'date' => 'required',
            'certificate_type' => 'required',
            'name' => 'required',
            'particular' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'ClearanceCertificate');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = ClearanceExpCertificate::query()->create($this->input);
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
    public function show(ClearanceExpCertificate $clearance_exp_certificate)
    {
        $data['certificate'] = $clearance_exp_certificate->load(['EmployeeOffboardingId','certificateType','created_by','updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClearanceExpCertificate $clearance_exp_certificate)
    {
        $request->validate([
            'employee_offboarding_id' => 'required',
            'date' => 'required',
            'certificate_type' => 'required',
            'name' => 'required',
            'particular' => 'required',
        ]);
        if($request->hasFile('attachment')) {
            $responce = $this->saveImages($request, 'ClearanceCertificate');
            if ($responce) {
                $this->input['attachment'] = $responce;
            }
        }
        try {
            DB::beginTransaction();
            $item = $clearance_exp_certificate->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClearanceExpCertificate $clearance_exp_certificate)
    {
        $item = $clearance_exp_certificate->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function clearanceCertificateDropDown()
    {
        $data['certificate_type']=Type::getTypeValues('certificate-type');
        $data['employee_offboardings']=EmployeeOffboarding::all();
        return resp('1', 'Record Created Successfully!', $data, Response::HTTP_OK);
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
}
