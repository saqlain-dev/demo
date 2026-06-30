<?php

namespace App\Http\Controllers\Api\V1\Program;

use App\Http\Controllers\Controller;
use App\Models\Program\LasRrfOutputIndicator;
use App\Models\Program\LasRrfOutputIndicatorTarget;
use App\Models\Program\ResultResourceFrameworkOutput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;


class ResultResourceFrameworkOutputController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('las_rrf_view');

        $data = ResultResourceFrameworkOutput::with(['lasSpDetail','outputIndicators.outputIndicatorsTarget'])->get();
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
                //'outcome_id' => 'required',
                'rrf_output_number' => 'required',
                'rrf_output_statement' => 'required',
                'sp_statement' => 'required',
                'rrf_output_indicator.*' => 'required'
            ]);
            $outputIndicators = $this->input['rrf_output_indicator'];
            unset($this->input['rrf_output_indicator']);

            $item = ResultResourceFrameworkOutput::query()->create($this->input);

            if ($item) {
                if (!empty($outputIndicators)){
                    foreach ($outputIndicators as $ind){
                        $ind['rrf_output_id'] = $item->id;
                        $target = $ind['target'];
                        unset($ind['target']);
                        //dd($ind);
                        $res = LasRrfOutputIndicator::query()->create($ind);
                        if ($res){
                            $res->outputIndicatorsTarget()->createMany($target);
                        }
                    }
                }
                //$item->goalIndicators()->createMany($goalIndicators);
                $item->load('outputIndicators.outputIndicatorsTarget')->get();
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

        $responce = ResultResourceFrameworkOutput::with(['lasSpDetail','outputIndicators.outputIndicatorsTarget'])->findOrFail($id);
        return resp('1', 'Successful!', $responce, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ResultResourceFrameworkOutput $output)
    {
        $this->authorize('las_rrf_update');

        try {
            DB::beginTransaction();
            $request->validate([
                'goal_id' => 'required',
                'outcome_id' => 'required',
                'rrf_output_number' => 'required',
                'rrf_output_statement' => 'required',
                'sp_statement' => 'required',
                'rrf_output_indicator.*' => 'required'
            ]);

            $outputIndicators = $this->input['rrf_output_indicator'];
            unset($this->input['rrf_output_indicator']);

            $item = $output->update($this->input);

            foreach ($outputIndicators as $indicator) {
                $targets = $indicator['target'];
                unset($indicator['target']);
                $res = LasRrfOutputIndicator::query()->updateOrCreate(['id' => $indicator['id']], $indicator);
                if ($res){
                    foreach ($targets as $target){
                        LasRrfOutputIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                    }
                }
            }
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
    public function destroy(ResultResourceFrameworkOutput $output): JsonResponse
    {
        $this->authorize('las_rrf_delete');

        $output->delete();
        $output->outputIndicators()->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }


    public function addOutputIndicators(Request $request)
    {
        $this->authorize('las_rrf_create');

        try {
        DB::beginTransaction();

        $request->validate([
            'rrf_output_id' => 'required',
            'sp_id' => 'required',
            'sp_indicator_id' => 'required',
            'output_indicator_number' => 'required',
            'baseline' => 'required',
            'lop_target' => 'required',
            'target' => 'required',
            'output_indicator_statement' => 'required'
        ]);
        $target = $this->input['target'];
        unset($this->input['target']);
        $item = LasRrfOutputIndicator::query()->create($this->input);
        if ($item){
            $item->outputIndicatorsTarget()->createMany($target);
        }
        DB::commit();
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    } catch (\Exception $e) {
        DB::rollback();
        // Log the error or handle it as needed
        return resp(0, 'Failed to Create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    }
}

    public function updateOutputIndicators(Request $request)
    {
        $this->authorize('las_rrf_update');

        try {
            DB::beginTransaction();
            $request->validate([
                'id' => 'required',
                //'rrf_outcome_id' => 'required',
                'sp_id' => 'required',
                'sp_indicator_id' => 'required',
                'output_indicator_number' => 'required',
                'baseline' => 'required',
                'lop_target' => 'required',
                'target' => 'required',
                'output_indicator_statement' => 'required'
            ]);
            $targets = $this->input['target'];
            unset($this->input['target']);
            $item = LasRrfOutputIndicator::query()->updateOrCreate(['id' => $this->input['id']], $this->input);
            if ($item){
                foreach ($targets as $target){
                    LasRrfOutputIndicatorTarget::query()->updateOrCreate(['id' => $target['id']], $target);
                }
            }
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            // Log the error or handle it as needed
            return resp(0, 'Failed to Update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteOutputIndicators($id)
    {
        $this->authorize('las_rrf_delete');

        $item = LasRrfOutputIndicator::query()->findOrFail($id)->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);

    }
    public function getOutputByOutcomeId($outcomeId)
    {
        $this->authorize('las_rrf_view');

        $outputs = ResultResourceFrameworkOutput::query()->with('outputIndicators')->where('outcome_id', $outcomeId)->get();
        return resp('1', 'Successful!', $outputs, Response::HTTP_OK);
    }
}
