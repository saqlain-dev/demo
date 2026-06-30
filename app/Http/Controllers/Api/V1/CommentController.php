<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\EventTask;
use App\Models\Finance\Audit\AprFollowUp;
use App\Models\Finance\Audit\AuditPlanReport;
use App\Models\Finance\Audit\ObservationReport;
use App\Models\Finance\Audit\TicketSchedule;
use App\Models\HR\Appraisal\PerformancePlanning;
use App\Models\HR\Appraisal\ScheduledCheckIn;
use App\Models\HR\Complaint\Complaint;
use App\Models\LearningLog\LearningLog;
use App\Models\Program\Project\ActivityCalendar;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\MnE\ProjectMneWorkplan;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\ResultResourceFramework;
use App\Models\Progress\ProgressWorkplan;
use App\Models\StrategicPlan;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\Quotation\Quotation;
use App\Models\RFQ\Rfq;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = Comment::query()->with('createdBy', 'commentable')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'comment' => 'required|string',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $type = $this->getModel($request->commentable_type);

            if (!$type) {
                return resp(0, 'Invalid commentable type!', [], Response::HTTP_BAD_REQUEST);
            }

            $this->input['commentable_type'] = $type;
            $comment = Comment::query()->create($this->input);

            if ($request->hasFile('attachment')) {

                $responce =$this->saveImages($request,'commentsAttach');
                if ($responce) {
                    $comment->update(['attachment' => $responce]);
                }

            }

            DB::commit();
            return resp(1, 'Successful!', $comment, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveImages($request,$folder){

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
    public function show(Comment $comment)
    {
        $comment->load('createdBy', 'commentable');
        return resp('1', 'Successful!', $comment, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $request->validate([
            'comment' => 'required|string',
        ]);
        try {
            DB::beginTransaction();

            $comment->update($request->except('attachment'));

            if ($request->hasFile('attachment')) {
                $responce =$this->saveImages($request,'commentsAttach');
                if ($responce) {
                    $comment->update(['attachment' => $responce]);
                }
            }

            DB::commit();
            return resp(1, 'Successful!', $comment->refresh(), Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }

    /**
     * Get the model class name based on a short type.
     */
    protected function getModel($type)
    {
        $models = [
            'complaint' => Complaint::class,
            'scheduled_check_in' => ScheduledCheckIn::class,
            'performance_planning' => PerformancePlanning::class,
            'observation_report' => ObservationReport::class,
            'apr_follow_up' => AprFollowUp::class,
            'ticket_schedule' => TicketSchedule::class,
            'audit_plan_report' => AuditPlanReport::class,
            'strategic_plan' => StrategicPlan::class,
            'rrf' => ResultResourceFramework::class,
            'project_profile' => ProjectProfile::class,
            'progress_work_plan' => ProgressWorkplan::class,
            'mne_plan' => ProjectMnePlan::class,
            'mne_work_plan' => ProjectMneWorkplan::class,
            'project_activity_calender' => ActivityCalendar::class,
            'learning_log' => LearningLog::class,
            'event_tasks' => EventTask::class,
            'customer' => Customer::class,
            'lead' => Lead::class,
            'opportunity' => Opportunity::class,
            'quotation' => Quotation::class,
            'rfq' => Rfq::class,
            // Add other models here
        ];

        return $models[$type] ?? null;
    }
}
