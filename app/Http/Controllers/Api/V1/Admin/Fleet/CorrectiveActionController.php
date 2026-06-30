<?php

namespace App\Http\Controllers\Api\V1\Admin\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\CorrectiveAction;
use App\Models\Admin\Fleet\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CorrectiveActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CorrectiveAction::all();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'incident_report_id' => 'required',
            'actions_items' => 'required',
            'date' => 'required',
            'person_responsible' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = CorrectiveAction::query()->create($this->input);
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
    public function show(CorrectiveAction $correctiveAction): JsonResponse
    {
        return resp('1', 'Successful!', $correctiveAction, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CorrectiveAction $correctiveAction)
    {
        $request->validate([
            'incident_report_id' => 'required',
            'actions_items' => 'required',
            'date' => 'required',
            'person_responsible' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $correctiveAction->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CorrectiveAction $correctiveAction): JsonResponse
    {
        $correctiveAction->delete();
        return resp('1', 'Record Deleted Successfully!', '', Response::HTTP_OK);
    }
}
