<?php

namespace App\Http\Controllers\Api\V1\Finance\SubGrants;

use App\Http\Controllers\Controller;
use App\Models\Finance\Grants\DueDelegence;
use App\Models\Finance\SubGrants\SubgrantPartners;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SubgrantPartnersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = SubgrantPartners::with(['created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'logo' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = SubgrantPartners::query()->create($this->input);
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
    public function show(SubgrantPartners $subgrantPartners): JsonResponse
    {
        $subgrantPartners = $subgrantPartners->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $subgrantPartners, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubgrantPartners $subgrantPartners)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'logo' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $subgrantPartners->update($this->input);
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
    public function destroy(SubgrantPartners $subgrantPartners): JsonResponse
    {
        $item = $subgrantPartners->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
