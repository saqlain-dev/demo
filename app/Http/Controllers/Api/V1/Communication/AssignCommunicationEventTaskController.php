<?php

namespace App\Http\Controllers\Api\V1\Communication;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\Communication\CommunicationEvent;
use App\Models\Communication\AssignCommunicationEventTask;
use App\Models\Communication\CommunicationEventDetail;

class AssignCommunicationEventTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('task_management_view');

        $data = AssignCommunicationEventTask::query()->with('event','task','assignedBy','assignedTo')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('task_management_create');

        $request->validate([
            'event_id' => 'required',
            'task_id' => 'required',
            'assigned_by' => 'required',
            'assigned_to' => 'required',
            'deadline' => 'nullable',
        ]);
        try {
            DB::beginTransaction();
            $item = AssignCommunicationEventTask::query()->create($request->all());
            $eventDetails = CommunicationEventDetail::findOrFail($item->task_id);
            $eventDetails->status = 1;
            $eventDetails->save();

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
        $this->authorize('task_management_view');

        $assignCommunicationTask = AssignCommunicationEventTask::findOrFail($id);
        $data['item'] = $assignCommunicationTask->load('event','task','assignedBy','assignedTo');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorize('task_management_update');

        $request->validate([
            'event_id' => 'required',
            'task_id' => 'required',
            'assigned_by' => 'required',
            'assigned_to' => 'required',
            'deadline' => 'nullable',
        ]);

        try {
            DB::beginTransaction();
            $assignCommunicationTask = AssignCommunicationEventTask::findOrFail($id);

            $assignCommunicationTask->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $assignCommunicationTask, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorize('task_management_delete');

        $assignCommunicationTask = AssignCommunicationEventTask::findOrFail($id);
        $assignCommunicationTask->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function employeeCommunicationEventView($id)
    {
        $this->authorize('task_management_view');

        $assignCommunicationTask = CommunicationEvent::findOrFail($id);
        $data['item'] = $assignCommunicationTask->with('event','task','assignedBy','assignedTo')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
