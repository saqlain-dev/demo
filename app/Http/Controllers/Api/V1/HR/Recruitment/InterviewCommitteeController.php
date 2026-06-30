<?php

namespace App\Http\Controllers\Api\V1\HR\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\HR\Recruitment\InterviewCommittee;
use App\Models\HR\Recruitment\InterviewCommitteeMember;
use App\Models\HR\Recruitment\ManageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InterviewCommitteeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = InterviewCommittee::with(['ApplyJobId','created_by','updated_by','InterviewCommitteeMembers.EmployeeId'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'interview_id' => 'required',
            'committee_name' => 'required',
            'committee_members.*' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $committee_members = $this->input['committee_members'];
            unset($this->input['committee_members']);
            $item = InterviewCommittee::query()->create($this->input);
            if ($item){
                $item->InterviewCommitteeMembers()->createMany($committee_members);
            }
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
    public function show(InterviewCommittee $interviewCommittee): JsonResponse
    {
        $manageJob = $interviewCommittee->load(['ApplyJobId','created_by','updated_by','InterviewCommitteeMembers.EmployeeId']);
        return resp('1', 'Successful!', $manageJob, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterviewCommittee $interviewCommittee)
    {
        $request->validate([
            'apply_job_id' => 'required',
            'interview_id' => 'required',
            'committee_name' => 'required',
            //'committee_members.*' => 'required',
        ]);
        try {
            // Update the interview committee details except committee members
            $item = $interviewCommittee->update($request->except('committee_members'));

            // Delete existing committee members
            $interviewCommittee->InterviewCommitteeMembers()->delete();

            // Add new committee members
            $committee_members = $request->input('committee_members');
            if (!empty($committee_members)) {
                foreach ($committee_members as $member) {
                    $interviewCommittee->InterviewCommitteeMembers()->create($member);
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateInterviewComments(Request $request, $id)
    {
        $members = InterviewCommitteeMember::query()->findOrFail($id);
        $request->validate([
            'comments' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $members->update($this->input);
            DB::commit();
            return resp('1', 'Comments Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InterviewCommittee $interviewCommittee): JsonResponse
    {
        $interviewCommittee->InterviewCommitteeMembers()->delete();
        $item = $interviewCommittee->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
