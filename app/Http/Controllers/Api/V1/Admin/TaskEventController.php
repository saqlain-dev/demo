<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventManagement;
use App\Models\EventTask;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskEventController extends Controller
{
    //index
    public function index()
    {
        $data = EventTask::query()->with('eventManagement', 'assignTo', 'flagStatus', 'taskStatus', 'createdBy', 'updatedBy')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    //store
    public function store(Request $request)
    {
        $data =$request->validate([
            'event_management_id' => 'required|exists:event_management,id',
            'task_date' => 'nullable|date',
            'assign_to' => 'nullable|exists:users,id',
            'flag_status' => 'nullable|exists:type_values,id',
            'task_status' => 'nullable|exists:type_values,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
            'description' => 'nullable|string',
        ]);

        $data['attachment'] = null;
        if ($request->hasFile('attachment')) {
            $data['attachment']= $this->saveFile($request,'eventTask');
        }

        $data = EventTask::create($data);
        return resp('1', 'Successful!', $data, Response::HTTP_CREATED);
    }

    //show
    public function show($id)
    {
        $data = EventTask::query()->with('eventManagement', 'assignTo', 'flagStatus', 'taskStatus', 'comments.createdBy', 'createdBy', 'updatedBy')->find($id);
        if (!$data) {
            return resp('0', 'Data not found!', null, Response::HTTP_NOT_FOUND);
        }
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, $id)
    {
        $data =$request->validate([
            'event_management_id' => 'nullable|exists:event_management,id',
            'task_date' => 'nullable|date',
            'assign_to' => 'nullable|exists:users,id',
            'flag_status' => 'nullable|exists:type_values,id',
            'task_status' => 'nullable|exists:type_values,id',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx',
            'description' => 'nullable|string',
        ]);

        $eventTask = EventTask::find($id);
        if (!$eventTask) {
            return resp('0', 'Data not found!', null, Response::HTTP_NOT_FOUND);
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $this->saveFile($request, 'eventTask');
        }

        $eventTask->update($data);
        return resp('1', 'Successful!', $eventTask, Response::HTTP_OK);
    }

    //destroy
    public function destroy($id)
    {
        $data = EventTask::find($id);
        if (!$data) {
            return resp('0', 'Data not found!', null, Response::HTTP_NOT_FOUND);
        }
        $data->delete();
        return resp('1', 'Deleted successfully!', null, Response::HTTP_OK);
    }

    //save file
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

    //event tasks dropdown
    public function eventTasksDropdown(){
        $data = [];
        $data['users'] = User::where('status', 1)->get();
        $data['flag_statuses'] = Type::getTypeValues('event_flag_status');
        $data['task_statuses'] = Type::getTypeValues('event_task_status');
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
}
