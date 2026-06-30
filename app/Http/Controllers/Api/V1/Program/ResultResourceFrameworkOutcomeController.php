<?php

namespace App\Http\Controllers\Api\V1\Program;

use App\Http\Controllers\Controller;
use App\Models\Program\LasRrfOutcomeIndicator;
use App\Models\Program\LasRrfOutcomeIndicatorTarget;
use App\Models\Program\ResultResourceFramework;
use App\Models\Program\ResultResourceFrameworkOutcome;
use App\Models\StrategicPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class ResultResourceFrameworkOutcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('las_rrf_view');

        $data = ResultResourceFrameworkOutcome::with(['lasSpDetail','outcomeIndicators.outcomeIndicatorsTarget'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('las_rrf_create');

        try {
            DB::beginTransaction();
            $request->validate([
                //'project_id' => 'required',
                'goal_id' => 'required',
                'rrf_outcome_number' => 'required',
                'rrf_outcome_statement' => 'required',
                'sp_statement' => 'required',
                'rrf_outcome_indicator.*' => 'required',
                //'rrf_indicator' => 'required',
            ]);
            $outcomeIndicators = $this->input['rrf_outcome_indicator'];
            unset($this->input['rrf_outcome_indicator']);
            $item = ResultResourceFrameworkOutcome::query()->create($this->input);
            if ($item) {
                if (!empty($outcomeIndicators)){
                    foreach ($outcomeIndicators as $ind){
                        $ind['rrf_outcome_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = LasRrfOutcomeIndicator::query()->create($ind);
                        if ($res){
                            $res->outcomeIndicatorsTarget()->createMany($target);
                        }
                    }
                }
                //$item->goalIndicators()->createMany($goalIndicators);
                $item->load('outcomeIndicators.outcomeIndicatorsTarget')->get();
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            // Log the error or handle it as needed
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        $this->authorize('las_rrf_view');

        $responce = ResultResourceFrameworkOutcome::with(['lasSpDetail','outcomeIndicators.outcomeIndicatorsTarget'])->findOrFail($id);
        return resp('1', 'Successful!', $responce, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResultResourceFrameworkOutcome $outcome)
    {
        $this->authorize('las_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'goal_id' => 'required',
                'rrf_outcome_number' => 'required',
                'rrf_outcome_statement' => 'required',
                'sp_statement' => 'required',
                'rrf_outcome_indicator.*' => 'required',
            ]);

            $outcomeIndicators = $this->input['rrf_outcome_indicator'];
            unset($this->input['rrf_outcome_indicator']);

            $item = $outcome->update($this->input);

            foreach ($outcomeIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = LasRrfOutcomeIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        LasRrfOutcomeIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                    }
                }
            }

//            foreach ($outcomeIndicators as $indicator) {
//                LasRrfOutcomeIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
//            }

            DB::commit();

            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();

            // Log the error or handle it as needed
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ResultResourceFrameworkOutcome $outcome): JsonResponse
    {
        $this->authorize('las_rrf_delete');

        $outcome->delete();
        $outcome->outcomeIndicators()->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }


    public function addOutcomeIndicators(Request $request)
    {
        $this->authorize('las_rrf_create');

        try {
            DB::beginTransaction();

            $request->validate([
                'rrf_outcome_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'outcome_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target' => 'required',
                'outcome_indicator_statement' => 'required'
            ]);



            $target = $this->input['target'];
            unset($this->input['target']);
            $item = LasRrfOutcomeIndicator::query()->create($this->input);
            if ($item){
                $item->outcomeIndicatorsTarget()->createMany($target);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateOutcomeIndicators(Request $request)
    {
        $this->authorize('las_rrf_update');

        try {
            DB::beginTransaction();

            $request->validate([
                'id' => 'required',
                //'rrf_outcome_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'outcome_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target' => 'required',
                'outcome_indicator_statement' => 'required'
            ]);


            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = LasRrfOutcomeIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    LasRrfOutcomeIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();

            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteOutcomeIndicators($id)
    {
        $this->authorize('las_rrf_delete');

        $item = LasRrfOutcomeIndicator::query()->findOrFail($id)->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);

    }

    public function getOutcomeByGoalId($goalId)
    {
        $this->authorize('las_rrf_view');

        $outcomes = ResultResourceFrameworkOutcome::query()->with('outcomeIndicators')->where('goal_id', $goalId)->get();
        return resp('1', 'Successful!', $outcomes, Response::HTTP_OK);
    }


}
