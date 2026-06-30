<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = TaxRate::query()->with(['created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            //'name' => 'required',
            'financial_year' => 'required',
            'payment_type' => 'required',
            'filer_rate' => 'required',
            'non_filer_rate' => 'required',
            'type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = TaxRate::query()->create($this->input);
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
    public function show(TaxRate $taxRate): JsonResponse
    {
        $taxRate = $taxRate->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $taxRate, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $request->validate([
            //'name' => 'required',
            'financial_year' => 'required',
            'payment_type' => 'required',
            'filer_rate' => 'required',
            'non_filer_rate' => 'required',
            'type' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $taxRate->update($this->input);
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
    public function destroy(TaxRate $taxRate): JsonResponse
    {
        $item = $taxRate->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
