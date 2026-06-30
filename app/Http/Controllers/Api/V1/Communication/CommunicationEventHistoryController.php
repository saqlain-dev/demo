<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Models\Admin\Tender;
use App\Models\Communication\CommunicationEventDetail;
use App\Models\Communication\CommunicationEventHistory;
use App\Models\Type;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Communication\EventCategory;
use App\Models\Communication\CommunicationEvent;
use Illuminate\Support\Facades\Storage;

class CommunicationEventHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CommunicationEventHistory::query()->with(['communicationEvent','communicationEventDetail'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'communication_event_id' => 'nullable|integer|exists:communication_events,id',
            'communication_event_detail_id' => 'nullable|integer|exists:communication_event_details,id',
            'status' => 'required',
            //'feedback' => 'required',
            //'attachment' => 'required|file',
        ]);
        try {
            DB::beginTransaction();

            $item = CommunicationEventHistory::query()->create($request->all());

            if($request->communication_event_id){
                CommunicationEvent::query()->find($request->communication_event_id)
                    ->update(['requester_response' => $request->status]);
            }
            if ($request->communication_event_detail_id){
                CommunicationEventDetail::query()->find($request->communication_event_detail_id)
                    ->update(['status' => $request->status]);
            }

            if ($request->hasFile('attachment')){
                $extension = $request->file('attachment')->getClientOriginalExtension();
                $attachmentPath = $request->file('attachment')->storeAs('images/communication', time() . '_attachment.' . $extension, 'public');

                $item->update([
                    'attachment' => $attachmentPath,
                ]);
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
    public function show(CommunicationEventHistory $communicationEventHistory)
    {
        $data['item'] = $communicationEventHistory->load(['communicationEvent','communicationEventDetail'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
    }


}
