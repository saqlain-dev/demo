<?php

namespace App\Http\Controllers\Api\V1\HR\Insurance;

use App\Http\Controllers\Controller;
use App\Models\HR\Insurance\EmployeeClaimReimbursement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EmployeeClaimReimbursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = EmployeeClaimReimbursement::query()->with(['insurance'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_insurance_id' => 'required|integer|exists:employee_insurances,id',
            'amount_claim' => 'required|integer',
            'submission_date' => 'required|date',
            'reimbursement_date' => 'required|date',
            'comments' => 'nullable',
        ]);
        
        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {

                $responce = $this->saveAttachment($request, 'employee_claim_reimbursement');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }
            $parent = EmployeeClaimReimbursement::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $parent, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveAttachment($request,$folder){

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
     * Display the specified resource.
     */
    public function show(EmployeeClaimReimbursement $EmployeeClaimReimbursement)
    {
        return resp(1, 'Successful!', $EmployeeClaimReimbursement->load('insurance'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeClaimReimbursement $EmployeeClaimReimbursement)
    {
        $request->validate([
            'employee_insurance_id' => 'required|integer|exists:employee_insurances,id',
            'amount_claim' => 'required|integer',
            'submission_date' => 'required|date',
            'reimbursement_date' => 'required|date',
            'comments' => 'nullable',
        ]);
        
        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {

                $responce = $this->saveAttachment($request, 'employee_claim_reimbursement');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }
            $parent = $EmployeeClaimReimbursement->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $EmployeeClaimReimbursement, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeClaimReimbursement $EmployeeClaimReimbursement)
    {
        $EmployeeClaimReimbursement->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }
}
