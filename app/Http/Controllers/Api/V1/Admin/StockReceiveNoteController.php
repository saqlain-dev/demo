<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\StockReceiveNote;
use App\Models\Admin\StockTransferNote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockReceiveNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = StockReceiveNote::query()->with(['ReceiveBy','StockTransferNoteId','created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_transfer_note_id' => 'required',
            'receive_date' => 'required',
            'receive_by' => 'required',
            'remarks' => 'required',
        ]);

        if ($request->hasFile('attachment')){
            $response = $this->saveImage($request, 'ReceiveNote');
            if ($response) {
                $this->input['attachment'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = StockReceiveNote::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
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
    public function show(StockReceiveNote $stockReceiveNote): JsonResponse
    {
        $logBook = $stockReceiveNote->load(['ReceiveBy','StockTransferNoteId','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockReceiveNote $stockReceiveNote)
    {
        $request->validate([
            'stock_transfer_note_id' => 'required',
            'receive_date' => 'required',
            'receive_by' => 'required',
            'remarks' => 'required',
        ]);

        if ($request->hasFile('attachment')){
            $response = $this->saveImage($request, 'ReceiveNote');
            if ($response) {
                $this->input['attachment'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = $stockReceiveNote->update($this->input);
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
    public function destroy(StockReceiveNote $stockReceiveNote): JsonResponse
    {
        $item = $stockReceiveNote->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
