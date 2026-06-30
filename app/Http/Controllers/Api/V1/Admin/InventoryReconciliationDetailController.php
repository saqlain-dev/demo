<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\InventoryReconciliationDetail;
use App\Models\Admin\Library\BookReconciliationDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InventoryReconciliationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['InventoryReconciliationDetail'] = InventoryReconciliationDetail::with(['InventoryReconciliationId','InventoryId','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'inventory_reconciliation_id' => 'required',
            'inventory_id' => 'required',
            'actual_qty' => 'required',
            'difference' => 'required',
            'remarks' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = InventoryReconciliationDetail::query()->create($this->input);
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
    public function show(InventoryReconciliationDetail $inventoryReconciliationDetail): JsonResponse
    {
        $book = $inventoryReconciliationDetail->load(['InventoryReconciliationId','InventoryId','created_by','updated_by']);
        return resp('1', 'Successful!', $book, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryReconciliationDetail $inventoryReconciliationDetail)
    {
        $request->validate([
            'inventory_reconciliation_id' => 'required',
            'inventory_id' => 'required',
            'actual_qty' => 'required',
            'difference' => 'required',
            'remarks' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $inventoryReconciliationDetail->update($this->input);
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
    public function destroy(InventoryReconciliationDetail $inventoryReconciliationDetail): JsonResponse
    {
        $item = $inventoryReconciliationDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
