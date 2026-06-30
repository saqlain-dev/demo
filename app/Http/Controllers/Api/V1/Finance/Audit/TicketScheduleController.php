<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Finance\Audit\TicketSchedule;

class TicketScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = TicketSchedule::with(['createdBy', 'employee', 'ticketStatus', 'auditSchedule'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'audit_schedule_id' => 'required|integer|exists:audit_schedules,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'deadline_date' => 'required',
            'scope' => 'required',
            //'remarks' => 'required',
            'ticket_status_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = TicketSchedule::query()->create($request->all());

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
        $ticketSchedule = TicketSchedule::with(['createdBy', 'employee', 'ticketStatus', 'auditSchedule.department','observationReport.employee','observationReport.comments.createdBy','comments.createdBy'])->findOrFail($id);
        return resp('1', 'Successful!', $ticketSchedule, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TicketSchedule $ticketSchedule)
    {
        $request->validate([
            'audit_schedule_id' => 'required|integer|exists:audit_schedules,id',
            'employee_id' => 'required|integer|exists:employees,id',
            'deadline_date' => 'required',
            'scope' => 'required',
            //'remarks' => 'required',
            'ticket_status_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $ticketSchedule->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $ticketSchedule->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TicketSchedule $ticketSchedule)
    {
        $ticketSchedule->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
