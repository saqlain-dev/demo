<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Models\Finance\CurrencyDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CurrencyDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = CurrencyDetail::with(['createdBy','currency'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|integer|exists:currencies,id',
            'date' => 'nullable|date',
            'unit_per_pkr' => 'required|numeric|min:0',
            'pkr_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $item = CurrencyDetail::query()->create($this->input);

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
    public function show($id)
    {
        $currencyDetail = CurrencyDetail::with(['createdBy','currency'])->findOrFail($id);
        return resp('1', 'Successful!', $currencyDetail, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CurrencyDetail $currencyDetail)
    {
        $request->validate([
            'currency_id' => 'required|integer|exists:currencies,id',
            'date' => 'nullable|date',
            'unit_per_pkr' => 'required|numeric|min:0',
            'pkr_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $currencyDetail->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $currencyDetail->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CurrencyDetail $currencyDetail)
    {
        $currencyDetail->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
