<?php

namespace App\Http\Controllers\Api\V1\Finance\Tax;

use App\Http\Controllers\Controller;
use App\Models\Finance\Tax\TaxSetting;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaxSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['tax_settings']=TaxSetting::query()->with('taxType','taxComputation','taxScope','taxGroup')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_name' => 'required',
            'tax_type' => 'required',
            'tax_computation' => 'required',
            'tax_scope' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = TaxSetting::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item->load('taxType','taxComputation','taxScope','taxGroup'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(TaxSetting $tax_settings)
    {
        return resp(1, 'Successful!', $tax_settings->load('taxType','taxComputation','taxScope','taxGroup'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxSetting $tax_settings)
    {
        $request->validate([
            'tax_name' => 'required',
            'tax_type' => 'required',
            'tax_computation' => 'required',
            'tax_scope' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $tax_settings->update($this->input);
            $tax_settings->refresh();

            DB::commit();
            return resp(1, 'Successful!', $tax_settings->load('taxType','taxComputation','taxScope','taxGroup'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxSetting $tax_settings)
    {
        $tax_settings->delete();
        return resp('1', 'Record deleted Successful!', $tax_settings, Response::HTTP_OK);
    }

    public function getTaxSettingsDropdowns()
    {
        $data['tax_types'] = Type::getTypeValues('tax-type');
        $data['tax_scope'] = Type::getTypeValues('tax-scope');
        $data['tax_computation'] = Type::getTypeValues('tax-computation');
        $data['tax_group'] = Type::getTypeValues('tax-group');
        return resp('1', ' Successful!', $data, Response::HTTP_OK);
    }
}
