<?php

namespace App\Http\Controllers\Api\V1\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Communication\CommunicationEvent;
use App\Models\Communication\CommunicationEventDetail;
use DB;

class EmployeeCommunicationEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $communicationEvent = CommunicationEvent::findOrFail($id);
        $data['item'] = $communicationEvent->load(['eventDetails']);
        $employeeId = Auth::user()->employee_id;
        $events = CommunicationEvent::whereHas('assignedTasks', function($query) use ($employeeId) {
            $query->where('assigned_to', $employeeId);
        })
        ->with(['assignedTasks.task'])
        ->get();

        $data['EventWithAssignedTasks'] = $events;
            return resp(1, 'Successful!', $data, Response::HTTP_OK);
        }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            DB::beginTransaction();
            $communicationEventDetails = CommunicationEventDetail::findOrFail($id);

        if ($request->hasFile('final_attachment')) {
            $responses = $this->saveAttachmentgFile($request, 'CommunicationEvent');

            $this->input['final_attachment'] = $responses;
        }

            $item = $communicationEventDetails->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveAttachmentgFile($request, $folder)
    {
        $image = $request->file('final_attachment');

        $path = 'uploads/media/' . $folder;

        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $filename = time() . '_' . $image->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $image->move($path, $file_name);

        $path = $path . '/' . $file_name;

        return $path;
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
