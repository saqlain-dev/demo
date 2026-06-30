<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Finance\LasInvoice;
use App\Models\Finance\LasInvoiceDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LasInvoiceDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = LasInvoiceDetail::query()->with(['created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'las_invoice_id' => 'required',
            'item' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = LasInvoiceDetail::query()->create($this->input);
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
    public function show(LasInvoiceDetail $lasInvoiceDetail): JsonResponse
    {
        $data['las_invoice_detail'] = $lasInvoiceDetail = $lasInvoiceDetail->load(['created_by','updated_by']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LasInvoiceDetail $lasInvoiceDetail)
    {
        $request->validate([
            'las_invoice_id' => 'required',
            'item' => 'required',
            'description' => 'required',
            'qty' => 'required',
            'unit' => 'required',
            'amount' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $lasInvoiceDetail->update($this->input);
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
    public function destroy(LasInvoiceDetail $lasInvoiceDetail): JsonResponse
    {
        $item = $lasInvoiceDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
