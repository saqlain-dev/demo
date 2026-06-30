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
                $responce = $this->saveFile($request, 'communication_event');

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
