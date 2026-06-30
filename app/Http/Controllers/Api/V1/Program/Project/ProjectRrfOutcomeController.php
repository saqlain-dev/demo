<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\Program\Project\ProjectRrfGoalIndicatorTarget;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutcomeIndicatorTarget;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ProjectRrfOutcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('project_rrf_view');

        $data = ProjectRrfOutcome::with(['ProOutcomeIndicators.proOutcomeIndicatorTargets','project_rrf_goal'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('project_rrf_create');

        try {
            DB::beginTransaction();

            $request->validate([
                'project_id' => 'required',
                'project_rrf_goal_id' => 'required',
                'outcome_number' => 'required',
                'outcome_statement' => 'required',
                'las_sp_statement' => 'required',
                'las_rrf_outcome_id' => 'required',
                'outcome_indicator.*' => 'required',
            ]);

            $outcomeIndicators = $this->input['outcome_indicator'];
            unset($this->input['outcome_indicator']);

            $item = ProjectRrfOutcome::query()->create($this->input);

            if ($item) {
                if (!empty($outcomeIndicators)){
                    foreach ($outcomeIndicators as $ind){
                        $ind['proj_rrf_outcome_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = ProjectRrfOutcomeIndicator::query()->create($ind);
                        if ($res){
                            $res->proOutcomeIndicatorTargets()->createMany($target);
                        }
                    }
                }
                //$item->ProGoalIndicators()->createMany($goalIndicators);
                $item->load('ProOutcomeIndicators.proOutcomeIndicatorTargets');
            }

//            if ($item) {
//                $item->ProOutcomeIndicators()->createMany($outcomeIndicators);
//                $item->load('ProOutcomeIndicators');
//            }

            DB::commit();

            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
}

    /**
     * Display the specified resource.
     */

    public function show(string $id): JsonResponse
    {
        $this->authorize('project_rrf_view');

        $responce = ProjectRrfOutcome::with('ProOutcomeIndicators.proOutcomeIndicatorTargets')->findOrFail($id);
        return resp('1', 'Successful!', $responce, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectRrfOutcome $outcome)
    {
        $this->authorize('project_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'project_id' => 'required',
                'project_rrf_goal_id' => 'required',
                'outcome_number' => 'required',
                'outcome_statement' => 'required',
                'las_sp_statement' => 'required',
                'las_rrf_outcome_id' => 'required',
                'outcome_indicator.*' => 'required',
            ]);

            $outcomeIndicators = $this->input['outcome_indicator'];
            unset($this->input['outcome_indicator']);

            $item = $outcome->update($this->input);

//            foreach ($outcomeIndicators as $indicator) {
//                ProjectRrfOutcomeIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
//            }

            foreach ($outcomeIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = ProjectRrfOutcomeIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        ProjectRrfOutcomeIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                    }
                }
            }

            DB::commit();

            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
}

    /**
     * Remove the specified resource from storage.
     */
//    public function destroy(ProjectRrfOutcome $outcome): JsonResponse
//    {
//        $output = $outcome->projectOutputs();
//        $outputIndicator = $output->ProOutputIndicators();
//
//
//        $outcome->delete();
//        $outcome->ProOutcomeIndicators()->delete();
//        //$item = $outcome->delete();
//        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
//    }

    public function destroy(ProjectRrfOutcome $outcome): JsonResponse
    {
        $this->authorize('project_rrf_delete');

        $outputs = $outcome->projectOutputs;
        $outputIndicators = $outputs->flatMap->ProOutputIndicators;

        // Check if any related outputs or output indicators exist
        if ($outputs->isNotEmpty()) {
            return resp('0', 'Record Cannot be Deleted! Outputs Exist', false, Response::HTTP_OK);
        }

        if ($outputIndicators->isNotEmpty()) {
            return resp('0', 'Record Cannot be Deleted! Output Indicators Exist', false, Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            // Delete related outcome indicators
            $outcome->ProOutcomeIndicators()->delete();

            // Delete the outcome
            $outcome->delete();

            DB::commit();
            return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to delete record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function addProjOutcomeIndicators(Request $request)
    {
        $this->authorize('project_rrf_create');

        try {
            DB::beginTransaction();
            $request->validate([
                'proj_rrf_outcome_id' => 'required',
                'sp_id' => 'required',
                'las_rrf_outcome_id' => 'required',
                'sp_indicator_id' => 'required',
                'outcome_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'outcome_indicator_statement' => 'required'
            ]);
            $target = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfOutcomeIndicator::query()->create($this->input);
            if ($item){
                $item->proOutcomeIndicatorTargets()->createMany($target);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateProjOutcomeIndicators(Request $request)
    {
        $this->authorize('project_rrf_update');

        try {
            DB::beginTransaction();
            $request->validate([
                'id' => 'required',
                //'proj_rrf_goal_id' => 'required',
                //'las_rrf_goal_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'outcome_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'outcome_indicator_statement' => 'required'
            ]);
            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfOutcomeIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    ProjectRrfOutcomeIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteProjOutcomeIndicators($id)
    {
        $this->authorize('project_rrf_delete');

        $outputIndicators = ProjectRrfOutputIndicator::query()->where('project_rrf_outcome_indicator_id', $id)->count();
        if ($outputIndicators > 0) {
            return resp('0', 'Record Cannot be Deleted! Output Indicator Exists', false, Response::HTTP_OK);
        }

        $item = ProjectRrfOutcomeIndicator::query()->find($id);
        if (!$item) {
            return resp('0', 'Record Not Found!', false, Response::HTTP_NOT_FOUND);
        }

        $item->delete();
        return resp('1', 'Record Deleted Successfully!', true, Response::HTTP_OK);
    }
    public function getOutcomeByGoalId($goalId)
    {
        $this->authorize('project_rrf_view');

        $outcomes = ProjectRrfOutcome::query()->with('ProOutcomeIndicators.proOutcomeIndicatorTargets')->where('project_rrf_goal_id', $goalId)->get();
        return resp('1', 'Successful!', $outcomes, Response::HTTP_OK);
    }
}
