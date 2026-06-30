<?php

namespace App\Http\Controllers\Api\V1\Program\Project;

use App\Http\Controllers\Controller;
use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\Program\Project\ProjectRrfGoalIndicatorTarget;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class ProjectRrfGoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('project_rrf_view');

        $data = ProjectRrfGoal::with('ProGoalIndicators.proGoalIndicatorTargets')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('project_rrf_create');

        $request->validate([
            'project_id' => 'required',
            'goal_number' => 'required',
            'goal_statement' => 'required',
            'las_sp_statement' => 'required',
            'las_rrf_goal_id' => 'required',
            'goal_indicator.*' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $goalIndicators = $this->input['goal_indicator'];
            unset($this->input['goal_indicator']);
            $item = ProjectRrfGoal::query()->create($this->input);
            if ($item) {
                if (!empty($goalIndicators)){
                    foreach ($goalIndicators as $ind){
                        $ind['proj_rrf_goal_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = ProjectRrfGoalIndicator::query()->create($ind);
                        if ($res){
                            $res->proGoalIndicatorTargets()->createMany($target);
                        }
                    }
                }
                //$item->ProGoalIndicators()->createMany($goalIndicators);
                $item->load('ProGoalIndicators.proGoalIndicatorTargets');
            }

            DB::commit();

            return resp('1', 'Record Created Successfully!', $item->refresh(), Response::HTTP_CREATED);
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

        $responce = ProjectRrfGoal::with('ProGoalIndicators.proGoalIndicatorTargets')->findOrFail($id);
        return resp('1', 'Successful!', $responce, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectRrfGoal $goal)
    {
        $this->authorize('project_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'project_id' => 'required',
                'goal_number' => 'required',
                'goal_statement' => 'required',
                'las_sp_statement' => 'required',
                'las_rrf_goal_id' => 'required',
                'goal_indicator.*' => 'required',
            ]);

            $goalIndicators = $this->input['goal_indicator'];
            unset($this->input['goal_indicator']);

            $item = $goal->update($this->input);

//            foreach ($goalIndicators as $indicator) {
//                ProjectRrfGoalIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
//            }

            foreach ($goalIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = ProjectRrfGoalIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        ProjectRrfGoalIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
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
//    public function destroy(ProjectRrfGoal $goal): JsonResponse
//    {
//        $outcomes = $goal->projectOutcomes();
//        $outcomeIndicator = $outcomes->ProOutcomeIndicators();
//        $output = $outcomes->projectOutputs();
//        $outputIndicator = $output->ProOutputIndicators();
//        $outcomeIndicator->delete();
//        $output->delete();
//        $outputIndicator->delete();
//        $output->delete();
//        $goal->ProGoalIndicators()->delete();
//        $goal->delete();
//        //$item = $goal->delete();
//        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
//    }

    public function destroy(ProjectRrfGoal $goal): JsonResponse
    {
        $this->authorize('project_rrf_delete');

        $outcomes = $goal->projectOutcomes;

        // Check if any related outcomes exist
        if ($outcomes->isNotEmpty()) {
            return resp('0', 'Record Cannot be Deleted! Outcomes Exist', false, Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            // Delete related goal indicators
            $goal->ProGoalIndicators()->delete();

            // Delete the goal
            $goal->delete();

            DB::commit();
            return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to delete record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function addProjGoalIndicators(Request $request)
    {
        $this->authorize('project_rrf_create');

        try {
            DB::beginTransaction();

            $request->validate([
                'proj_rrf_goal_id' => 'required',
                'las_rrf_goal_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'goal_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'goal_indicator_statement' => 'required'
            ]);
            $target = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfGoalIndicator::query()->create($this->input);
            if ($item){
                $item->proGoalIndicatorTargets()->createMany($target);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateProjGoalIndicators(Request $request)
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
                'goal_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target.*' => 'required',
                'goal_indicator_statement' => 'required'
            ]);
            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = ProjectRrfGoalIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    ProjectRrfGoalIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteProjGoalIndicators($id)
    {
        $this->authorize('project_rrf_delete');

        $outcomeIndicators = ProjectRrfOutcomeIndicator::query()->where('project_rrf_goal_indicator_id', $id)->count();
        if ($outcomeIndicators > 0) {
            return resp('0', 'Record Cannot be Deleted! Outcome Indicator Exists', false, Response::HTTP_OK);
        }

        $item = ProjectRrfGoalIndicator::query()->find($id);
        if (!$item) {
            return resp('0', 'Record Not Found!', false, Response::HTTP_NOT_FOUND);
        }

        $item->delete();
        return resp('1', 'Record Deleted Successfully!', true, Response::HTTP_OK);

    }
}
