<?php

namespace App\Http\Controllers\Api\V1\PDU;

use App\Http\Controllers\Api\V1\Program\Project\ProjectProfileController;
use App\Http\Controllers\Controller;
use App\Models\CheckInSheet;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response; 
use Illuminate\Support\Facades\Auth;
class CheckInSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'check_in_sheet',
            'manage_audit_program_delivery_unit',
        ]);

        $data['checkInSheets']= CheckInSheet::with('activity')->where('type',1)->get();
        $data['redFlagSheets']= CheckInSheet::with('activity')->where('type',2)->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
    public function projectByID(ProjectProfile $item){
        $this->authorizeAny([
            'check_in_sheet',
            'manage_audit_program_delivery_unit',
        ]);

        $data = $item->load([
            'pdu_focal_person',
            'project_manager',
            'projectGoals' =>
                [
                    'ProGoalIndicators' =>['proWorkPlanIndicators' => ['activities' =>['checkinsheetactivities'=>['created_by','updated_by']]]],
                    'projectOutcomes' => [
                        'ProOutcomeIndicators' => ['proWorkPlanIndicators' => ['activities' =>['checkinsheetactivities'=>['created_by','updated_by']]]],
                        'projectOutputs' => ['ProOutputIndicators' => ['proWorkPlanIndicators' => ['created_by','updated_by','activities' =>['checkinsheetactivities'=>['created_by','updated_by']]]],]
                    ]
                ]
        ]);
        $data['donors'] = $item->donors ?? null;
        $data['pdu_focal_person_id'] = $item->pdu_focal_person ?? null;
        $data['project_manager_id'] = $item->project_manager ?? null;

        //$data = $item->load(['projectOutputs' => ['ProOutputIndicators' => ['proWorkPlanIndicators' => ['proWorkplanIndicatorProgress','activities' =>['checkinsheetactivities']]]]]);
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('check_in_sheet');

        $request->validate([
            'project_id' => 'required',
            'tracking' => 'required',
            'status' => 'required',
        ]);
        $this->input['start_date']=date('Y-m-d',strtotime($request->start_date));
        $this->input['end_date']=date('Y-m-d',strtotime($request->end_date));
        $checkInSheet= CheckInSheet::query()->create($this->input);
        return resp(1,'Successful!', $checkInSheet,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(CheckInSheet $checkInSheet)
    {
        $this->authorize('check_in_sheet');

        $checkInSheets= CheckInSheet::with('activity')->where('id',$checkInSheet->id)->get();
        return resp(1,'Successful!', $checkInSheets,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CheckInSheet $checkInSheet)
    { 
        $this->authorize('check_in_sheet');

        $request->validate([
            'project_id' => 'required',
            'tracking' => 'required',
            'status' => 'required',
        ]);
        $this->input['start_date']=date('Y-m-d',strtotime($request->start_date));
        $this->input['end_date']=date('Y-m-d',strtotime($request->end_date)); 
        $this->input['updated_by']=Auth::id();
        $checkIn=CheckInSheet::query()->where('id', $checkInSheet->id)->update($this->input);
        $checkInSheet=CheckInSheet::with('activity')->findOrFail($checkInSheet->id);
        if( $checkInSheet->activity){ 
            $checkInSheet->activity->update([
                'updated_by'=>Auth::id()
            ]);
        }
        $checkInSheet=CheckInSheet::with('activity')->findOrFail($checkInSheet->id); 
        return resp(1,'Successful!', $checkInSheet,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CheckInSheet $checkInSheet)
    {
        $this->authorize('check_in_sheet');

        $checkInSheet->delete();
        $message="Progress Deleted Successfully";
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
