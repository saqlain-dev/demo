<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Task\Task;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employeeIds=getReportToEmployees(auth()->user()->employee_id);
        $userIds=User::query()->whereIn('employee_id',$employeeIds ?? [])->pluck('id');
        $data['task_listing']=Task::query()->with('assignTo','taskStatus','taskPriority','lead')->whereIn('created_by',$userIds ?? [])->orWhereIn('assign_to',$employeeIds ?? [])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'required|string',
            'task_status' => 'required|integer',
            'assign_to' => 'required|integer',
            'task_priority' => 'required|integer',
            'due_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $assignToemployee=$request->assign_to;
            $statement = DB::select("SELECT IDENT_CURRENT('tasks') as nextID");
            $task_number='TID-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['task_no']=$task_number;
            $task=Task::query()->create($this->input);

            $title="Task Assigned!";
            $message="You have been assigned new task.";

                sendWebNotification([$assignToemployee], $title, $message,'task-assignment',$task);

            DB::commit();
            return resp(1, 'Successful!', $task->load('assignTo','taskStatus','taskPriority','lead'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return resp(1, 'Successful!', $task->load('assignTo','taskStatus','taskPriority','lead'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
            'task_description' => 'required|string',
            'task_status' => 'required|integer',
            'assign_to' => 'required|integer',
            'task_priority' => 'required|integer',
            'due_date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $task->update($this->input);
            $task->refresh();
            DB::commit();
            return resp(1, 'Successful!', $task->load('assignTo','taskStatus','taskPriority','lead'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {

        $task->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getTaskDropdowns()
    {
        $employeeIds=getReportToEmployees(auth()->user()->employee_id);

        $data['task_status']=Type::getTypeValues('task-status');
        $data['task_priority']=Type::getTypeValues('task-priority');
        $data['department']= Type::getTypeValues('department-names');
        $data['employees']=Employee::query()->whereIn('id',$employeeIds ?? [])->with('salesTeamEmployee')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function updateTaskStatus(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer',
            'task_status' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();
            Task::query()->where('id',$request->task_id)->update(['task_status'=>$request->task_status]);
            $task=Task::query()->with('assignTo','taskStatus','taskPriority','lead')->where('id',$request->task_id)->first();

            DB::commit();
            return resp(1, 'Successful!', $task, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
}
