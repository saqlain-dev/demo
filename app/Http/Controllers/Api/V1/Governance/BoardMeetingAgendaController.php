<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\Governance\BoardMeeting;
use App\Models\Governance\BoardMeetingAgenda;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BoardMeetingAgendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agenda_listing=BoardMeetingAgenda::all()->toArray();
        foreach ($agenda_listing as $key => $agenda)
        {

            $list=$this->getAgendaCategoryDetail($agenda['agenda_category']);
            $agenda_listing[$key]['agenda_category']=$list;

        }
        $data['agenda_listing']=$agenda_listing;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            $request->validate([
                'agenda_title' => 'required',
                'agenda_category' => 'required|array|min:1',
            ]);
            $this->input['agenda_category']=implode(',',$request->agenda_category);

           $agenda= BoardMeetingAgenda::query()->create($this->input);
            DB::commit();
            return resp('1', 'Agenda added Successfully!', $agenda, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }


    /**
     * Display the specified resource.
     */
    public function show(BoardMeetingAgenda $agenda)
    {

        $Updateagenda=$agenda->toArray();
        foreach($Updateagenda as $key=>  $agendarow){

            if($key == 'agenda_category'){
                $list=$this->getAgendaCategoryDetail($Updateagenda[$key]);
                $Updateagenda[$key]=$list;
            }

        }
        $data['agenda_detail']=$Updateagenda;

        $data['approval_request']=getNextApproval(27,auth()->user()->designation_id,$agenda->id);
        $data['approval_request_status']=checkApprovalRequestStatus(27,$agenda->id);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BoardMeetingAgenda $agenda)
    {

        try {

            DB::beginTransaction();
            $request->validate([
                'agenda_title' => 'required',
                'agenda_category' => 'required|array|min:1',
            ]);
            $this->input['agenda_category']=implode(',',$request->agenda_category);

           BoardMeetingAgenda::query()->find($agenda->id)->update($this->input);
            DB::commit();
            $agenda->refresh();
            $Updateagenda=$agenda->toArray();
            foreach($Updateagenda as $key=>  $agenda){

                if($key == 'agenda_category'){
                    $list=$this->getAgendaCategoryDetail($Updateagenda[$key]);
                    $Updateagenda[$key]=$list;
                }

            }

            return resp('1', 'Agenda updated Successfully!', $Updateagenda, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAgendaCategoryDetail($category)
    {

        $categories=explode(',',$category);

        $categories=TypeValue::query()->whereIn('id',$categories)->get();

        return $categories ? $categories->toArray():[];

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardMeetingAgenda $agenda)
    {
        $boardMeeting=BoardMeeting::query()->where('agenda_id',$agenda->id)->get();
        if(!$boardMeeting){
            $agenda->delete();
            return resp(1, 'Agenda deleted successfully.', [], Response::HTTP_OK);
        }else{
            return resp(0, 'Board meeting added against this agenda.', [], Response::HTTP_OK);
        }
    }

    public function agendaDropDown()
    {
        $data['governance_category_list']=Type::getTypeValues('governance-category');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function sendAgendaForApproval(BoardMeetingAgenda $item)
    {

        $approval_process=ApprovalProcess::query()->where('approval_process_id',27)->get();
        $checkProcess=ApprovalProcessList::query()->where('approval_process_id',27)->where('approval_request_status',1)->where('request_module_id',$item->id)->count();
        if($approval_process->count() > 0 && $checkProcess == 0){

            foreach ($approval_process as $approval){
                $insert=array(
                    'approval_process_id'=>$approval['approval_process_id'],
                    'designation_id'=>$approval['designation_id'],
                    'process_order'=>$approval['process_order'],
                    'request_module_id'=>$item->id,
                );
                $Approval=ApprovalProcessList::query()->create($insert);

            }
            $update=array('approval_status'=>2);
            BoardMeetingAgenda::query()->where('id',$item->id)->update($update);
            return resp(1,'Agenda send for Approval.', $Approval,Response::HTTP_OK);
        }else{


            if( $checkProcess == 0){
                return resp(0,'Approval process not available', [],Response::HTTP_OK);
            }else{
                return resp(0,'Agenda approval already sent.', [],Response::HTTP_OK);
            }
        }
    }
}
