<?php

namespace App\Http\Controllers\Api\V1\Finance\Grants;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Finance\Grants\DueDelegence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DueDelegenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = DueDelegence::with(['NofoId.NofoDetail','DueDelegenceDetail','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nofo_id' => 'required',
            'name' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = DueDelegence::query()->create($this->input);
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
    public function show(DueDelegence $dueDelegence): JsonResponse
    {
        $dueDelegence = $dueDelegence->load(['NofoId.NofoDetail','DueDelegenceDetail','created_by','updated_by']);
        return resp('1', 'Successful!', $dueDelegence, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DueDelegence $dueDelegence)
    {
        $request->validate([
            'nofo_id' => 'required',
            'name' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $dueDelegence->update($this->input);
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
    public function destroy(DueDelegence $dueDelegence): JsonResponse
    {
        $dueDelegence->DueDelegenceDetail()->delete();
        $item = $dueDelegence->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
