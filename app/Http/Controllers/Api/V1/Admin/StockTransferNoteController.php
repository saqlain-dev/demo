<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\IssueStock;
use App\Models\Admin\IssueStockDetail;
use App\Models\Admin\StockRequest;
use App\Models\Admin\StockTransferNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockTransferNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'stock_transfer_note_view',
            'manage_audit_inventory_warehouse',
        ]);

        $data = StockTransferNote::query()->with(['TransferBy','IssueStockId.StockRequestId','IssueStockId.StockRequestId.RequestedBy','IssueStockId.StockRequestId.DepartmentId','IssueStockDetailId','transferFrom','transferTo','created_by','updated_by','receivingNote.ReceiveBy'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'stock_transfer_note_create'
        ]);

        $request->validate([
            'issue_stock_id' => 'required',
            //'issue_stock_detail_id' => 'required',
            'transfer_date' => 'required',
            'transfer_by' => 'required',
            'remarks' => 'required',
        ]);

        if ($request->hasFile('attachment')){

            $response = $this->saveImage($request, 'TransferNote');

            if ($response) {
                $this->input['attachment'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = StockTransferNote::query()->create($this->input);
            if($item){
                IssueStock::query()->where('id',$request->issue_stock_id)->update(array('is_transfer_note_generated'=>1));
                $issue_stock = IssueStock::find($request->issue_stock_id);
                StockRequest::where('id', $issue_stock->stock_request_id)->update(['status' => 1]);
            }
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

        $file = $request->file('attachment');

        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;
    }

    /**
     * Display the specified resource.
     */
    public function show(StockTransferNote $stockTransferNote): JsonResponse
    {
        $this->authorizeAny([
            'stock_transfer_note_view',
            'manage_audit_inventory_warehouse',
        ]);

        $logBook = $stockTransferNote->load(['TransferBy','IssueStockId.StockRequestId','IssueStockId.StockRequestId.RequestedBy','IssueStockId.StockRequestId.DepartmentId', 'IssueStockId.IssueStockDetail','IssueStockDetailId','transferFrom','transferTo','created_by','updated_by','receivingNote.ReceiveBy']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockTransferNote $stockTransferNote)
    {
        $this->authorizeAny([
            'stock_transfer_note_update'
        ]);

        $request->validate([
            'issue_stock_id' => 'required',
            //'issue_stock_detail_id' => 'required',
            'transfer_date' => 'required',
            'transfer_by' => 'required',
            'remarks' => 'required',
        ]);

        if ($request->hasFile('attachment')){
            $response = $this->saveImage($request, 'TransferNote');
            if ($response) {
                $this->input['attachment'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = $stockTransferNote->update($this->input);
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
    public function destroy(StockTransferNote $stockTransferNote): JsonResponse
    {
        $this->authorizeAny([
            'stock_transfer_note_delete'
        ]);

        $item = $stockTransferNote->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
