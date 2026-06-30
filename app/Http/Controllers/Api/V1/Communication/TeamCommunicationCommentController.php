<?php

namespace App\Http\Controllers\Api\V1\Communication;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Communication\TeamCommunicationComment;

class TeamCommunicationCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = TeamCommunicationComment::query()->with('createdBy')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:communication_event_details,id',
            'comment' => 'nullable|string',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            $item = TeamCommunicationComment::query()->create($request->all());

            if ($request->hasFile('attachment')){
                $extension = $request->file('attachment')->getClientOriginalExtension();
                $attachmentPath = $request->file('attachment')->storeAs('images/communication_event', time() . '_attachment.' . $extension, 'public');
                $item->update(['attachment' => $attachmentPath]);
            }

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
    public function show(TeamCommunicationComment $teamCommunicationComment)
    {
        $teamCommunicationComment->load('createdBy');
        return resp('1', 'Successful!', $teamCommunicationComment, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamCommunicationComment $teamCommunicationComment)
    {
        $request->validate([
            'task_id' => 'required|integer',
            'comment' => 'required|string',
            //'attachment' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx',
        ]);
        try {
            DB::beginTransaction();

            $item = $teamCommunicationComment->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamCommunicationComment $teamCommunicationComment)
    {
        $teamCommunicationComment->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
