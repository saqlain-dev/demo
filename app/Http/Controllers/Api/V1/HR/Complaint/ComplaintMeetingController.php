<?php

namespace App\Http\Controllers\Api\V1\HR\Complaint;

use App\Http\Controllers\Controller;
use App\Models\HR\Complaint\ComplaintMeeting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ComplaintMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['complaint_listing'] = ComplaintMeeting::query()->with('committeeMembers')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'complaint_id' => 'required',
            'meeting_date' => 'required',
            'meeting_time' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = ComplaintMeeting::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ComplaintMeeting $complaint_meeting)
    {
        return resp('1', 'Successful!', $complaint_meeting->load('committeeMembers'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ComplaintMeeting $complaint_meeting)
    {
        $request->validate([
            'complaint_id' => 'required',
            'meeting_date' => 'required',
            'meeting_time' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $complaint_meeting->update($this->input);
            $complaint_meeting->refresh();
            DB::commit();
            return resp('1', 'Record Created Successfully!', $complaint_meeting, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ComplaintMeeting $complaint_meeting)
    {
        $item = $complaint_meeting->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
