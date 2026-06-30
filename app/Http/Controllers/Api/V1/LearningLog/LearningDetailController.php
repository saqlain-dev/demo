<?php

namespace App\Http\Controllers\Api\V1\LearningLog;

use App\Http\Controllers\Controller;
use App\Models\LearningLog\LearningDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LearningDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['learning_log']=LearningDetail::query()->with('followedBy','learningTheme')->get();
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'learning_log_id' => 'required',
            'learning_theme' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $item = LearningDetail::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LearningDetail $learning_log_detail)
    {
        $data['learning_log']=$learning_log_detail->load('followedBy','learningTheme');
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LearningDetail $learning_log_detail)
    {
        $request->validate([
            'learning_log_id' => 'required',
            'learning_theme' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $learning_log_detail->update($this->input);
            $learning_log_detail->refresh();

            DB::commit();
            return resp(1, 'Successful!', $learning_log_detail->load('followedBy','learningTheme'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningDetail $learning_log_detail)
    {
        $learning_log_detail->delete();
        return resp('1', 'Record deleted Successful!', $learning_log_detail, Response::HTTP_OK);
    }
}
