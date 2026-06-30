<?php

namespace App\Http\Controllers\Api\V1\Finance\SubGrants;

use App\Http\Controllers\Controller;
use App\Models\Finance\SubGrants\SubGrant;
use App\Models\Finance\SubGrants\SubGrantDueDeligence;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SubGrantDueDeligenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SubGrantDueDeligence::with(['SubGrantId','created_by','updated_by','SgDueDelegenceDetail'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sub_grant_id' => 'required',
            'name' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = SubGrantDueDeligence::query()->create($this->input);
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
    public function show(SubGrantDueDeligence $subGrantDueDeligence): JsonResponse
    {
        $subGrantDueDeligence = $subGrantDueDeligence->load(['SubGrantId','created_by','updated_by','SgDueDelegenceDetail']);
        return resp('1', 'Successful!', $subGrantDueDeligence, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubGrantDueDeligence $subGrantDueDeligence)
    {
        $request->validate([
            'sub_grant_id' => 'required',
            'name' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $subGrantDueDeligence->update($this->input);
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
    public function destroy(SubGrantDueDeligence $subGrantDueDeligence): JsonResponse
    {
        $item = $subGrantDueDeligence->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
