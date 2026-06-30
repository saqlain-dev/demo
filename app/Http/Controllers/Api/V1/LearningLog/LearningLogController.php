<?php

namespace App\Http\Controllers\Api\V1\LearningLog;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LearningLog\LearningLog;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LearningLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['learning_log']=LearningLog::query()->with(['department','created_by','comments.createdBy','learningDetail'=>['learningTheme','followedBy']])->get();
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
            'quarter' => 'required',
            'department' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = LearningLog::query()->create($this->input);

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
    public function show(LearningLog $learning_log)
    {
        $data['learning_log']=$learning_log->load(['department','created_by','learningDetail'=>['learningTheme','followedBy']]);
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LearningLog $learning_log)
    {
        $request->validate([
            'date' => 'required|date|date_format:Y-m-d',
            'quarter' => 'required',
            'department' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $learning_log->update($this->input);
            $learning_log->refresh();

            DB::commit();
            return resp(1, 'Successful!', $learning_log->load('department','learningDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LearningLog $learning_log)
    {
        $learning_log->learningDetail()->delete();
        $learning_log->delete();
        return resp('1', 'Record deleted Successful!', $learning_log, Response::HTTP_OK);
    }

    public function getLearningLogDropdowns()
    {
        $data['departments'] = Type::getTypeValues('department-names');
        $data['learning_themes'] = Type::getTypeValues('learning-log-theme');
        $data['employee'] = Employee::query()->with('department')->whereNotIn('employee_type', [14, 16, 17, 18])->get();
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }
}
