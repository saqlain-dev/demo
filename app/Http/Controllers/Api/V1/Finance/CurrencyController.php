<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Models\Finance\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = Currency::with(['createdBy', 'currencyDetails'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'current_rate' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $item = Currency::query()->create($request->all());

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
        $currency = Currency::with(['createdBy','currencyDetails'])->findOrFail($id);
        return resp('1', 'Successful!', $currency, Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currency $currency)
    {
        $request->validate([
            'currency' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'current_rate' => 'numeric|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $currency->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $currency->refresh(), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        $currency->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
