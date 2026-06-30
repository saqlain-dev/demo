<?php

namespace App\Http\Controllers\Api\V1\Finance\Audit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Finance\Audit\AuditSchedule;

class AuditScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = AuditSchedule::with(['createdBy', 'department', 'auditPlan'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'audit_plan_id' => 'required|integer|exists:audit_plans,id',
            'department_id' => 'required|integer',
            'deadline_date' => 'required',
            'scope' => 'required',
            //'remarks' => 'required',
            'objective' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = AuditSchedule::query()->create($request->all());

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
        $auditSchedule = AuditSchedule::with(['createdBy', 'department', 'auditPlan','ticketSchedule'=>['employee','ticketStatus','observationReport'=>['createdBy','employee']],'auditPlanReport.preparedBy','auditPlanReport.auditPlanStatus'])->findOrFail($id);
        return resp('1', 'Successful!', $auditSchedule, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuditSchedule $auditSchedule)
    {
        $request->validate([
            'audit_plan_id' => 'required|integer|exists:audit_plans,id',
            'department_id' => 'required|integer',
            'deadline_date' => 'required',
            'scope' => 'required',
            //'remarks' => 'required',
            'objective' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $auditSchedule->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $auditSchedule->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuditSchedule $auditSchedule)
    {
        $auditSchedule->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
