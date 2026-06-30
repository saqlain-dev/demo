<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Finance\Audit\ObservationReport;
use Illuminate\Support\Facades\Storage;

class ObservationReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = ObservationReport::with(['createdBy', 'employee', 'ticketSchedule', 'comments.createdBy'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'ticket_schedule_id' => 'required|integer|exists:ticket_schedules,id',
            'attachment' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $item = ObservationReport::query()->create($request->all());

            if ($request->hasFile('attachment')){
                $responce = $this->saveFile($request, 'observation_reports');

                if ($responce) {
                    $item->update(['attachment' => $responce]);
                }
            }

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }
    public function saveFile($request,$folder){

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
    public function show($id)
    {
        $observationReport = ObservationReport::with(['createdBy', 'employee', 'ticketSchedule', 'comments.createdBy'])->findOrFail($id);
        return resp('1', 'Successful!', $observationReport, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ObservationReport $observationReport)
    {
        $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'ticket_schedule_id' => 'required|integer|exists:ticket_schedules,id',
            'attachment' => 'required|file',
        ]);

        try {
            DB::beginTransaction();

            $observationReport->update($this->input);


            if ($request->hasFile('attachment')){
                $responce = $this->saveFile($request, 'observation_reports');

                if ($responce) {
                    $observationReport->update(['attachment' => $responce]);
                }
            }

            DB::commit();
            return resp(1, 'Successful!', $observationReport->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ObservationReport $observationReport)
    {
        $observationReport->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
