<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Finance\Audit\AprFollowUp;
use Illuminate\Validation\Rule;

class AprFollowUpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = AprFollowUp::with(['createdBy','followUpStatus', 'auditPlanReport', 'comments.createdBy','priority','responsiblePerson'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'audit_plan_report_id' => 'required|integer|exists:audit_plan_reports,id',
            'deadline_date' => 'required',
            'observation' => 'required',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'apr_follow_up_status_id' => 'required',
            'checkin_date' => 'date|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();

            if($request->file('attachment')){
                $responce=$this->saveFile($request,'apr_follow_ups');
                $this->input['attachment']=$responce;
            }

            $item = AprFollowUp::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $aprFollowUp = AprFollowUp::with(['createdBy','followUpStatus', 'auditPlanReport.auditPlanStatus','priority', 'comments.createdBy','responsiblePerson'])->findOrFail($id);
        return resp('1', 'Successful!', $aprFollowUp, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AprFollowUp $aprFollowUp)
    {
        $request->validate([
            'audit_plan_report_id' => [
                'required',
                'integer',
                Rule::unique('apr_follow_ups', 'audit_plan_report_id')->ignore($aprFollowUp->id),
            ],
            'deadline_date' => 'required',
            'observation' => 'required',
            //'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'apr_follow_up_status_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->file('attachment')){
                $responce=$this->saveFile($request,'apr_follow_ups');
                $this->input['attachment']=$responce;
            }

            $aprFollowUp->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $aprFollowUp->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AprFollowUp $aprFollowUp)
    {
        $aprFollowUp->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    public function saveFile($request,$folder)
    {
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
