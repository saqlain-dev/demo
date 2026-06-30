<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\CommunicationComment;
use App\Models\Communication\EventCategory;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommunicationCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = CommunicationComment::query()->with('createdBy')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'nullable|integer|exists:communication_events,id',
            'apply_job_id' => 'nullable|integer|exists:apply_jobs,id',
            'manage_job_id' => 'nullable|integer|exists:manage_jobs,id',
            'comment' => 'nullable|string',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            $item = CommunicationComment::query()->create($request->all());

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
    public function show(CommunicationComment $communicationComment)
    {
        $communicationComment->load('createdBy');
        return resp('1', 'Successful!', $communicationComment, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunicationComment $communicationComment)
    {
        $request->validate([
            'event_id' => 'nullable|integer|exists:communication_events,id',
            'apply_job_id' => 'nullable|integer|exists:apply_jobs,id',
            'manage_job_id' => 'nullable|integer|exists:manage_jobs,id',
            'comment' => 'required|string',
            //'attachment' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx',
        ]);
        try {
            DB::beginTransaction();

            $item = $communicationComment->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $communicationComment->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunicationComment $communicationComment)
    {
        $communicationComment->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
