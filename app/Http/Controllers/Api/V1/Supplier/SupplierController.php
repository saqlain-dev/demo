<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Supplier\Supplier;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['supplier_list']=Supplier::query()->with('supplierGroup','supplierType','country')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required',
            'supplier_type' => 'required',
            'supplier_group' => 'required',
            'country' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $supplier=Supplier::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $supplier->load('supplierGroup','supplierType','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return resp(1, 'Successful!', $supplier->load('supplierGroup','supplierType','country'), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'supplier_name' => 'required',
            'supplier_type' => 'required',
            'supplier_group' => 'required',
            'country' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $supplier->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $supplier->load('supplierGroup','supplierType','country'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getSupplierDropDown()
    {
        $data['supplier_group']=Type::getTypeValues('supplier-group');
        $data['supplier_type']=Type::getTypeValues('supplier-type');
        $data['country_list']=Country::query()->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }
}
