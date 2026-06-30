<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\IssueStockDetail;
use App\Models\Admin\StockRequestDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class IssueStockDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = IssueStockDetail::query()->with(['IssueStockId','ItemId','created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'issue_stock_id' => 'required',
            'item_id' => 'required',
            'qty' => 'required',
            'remarks' => 'required',
            'policy_document' => 'required',
        ]);

        if ($request->hasFile('policy_document')){
            $response = $this->saveImage($request['policy_document'], 'policy_document');
            if ($response) {
                $this->input['policy_document'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = IssueStockDetail::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

        $file = $request->file('policy_document');
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
    public function show(IssueStockDetail $issueStockDetail): JsonResponse
    {
        $logBook = $issueStockDetail->load(['IssueStockId','ItemId','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IssueStockDetail $issueStockDetail)
    {
        $request->validate([
            'issue_stock_id' => 'required',
            'item_id' => 'required',
            'qty' => 'required',
            'remarks' => 'required',
            //'policy_document' => 'required',
        ]);

        if ($request->hasFile('policy_document')){
            $response = $this->saveImage($request['policy_document'], 'policy_document');
            if ($response) {
                $this->input['policy_document'] = $response;
            }
        }
        try {
            DB::beginTransaction();
            $item = $issueStockDetail->update($this->input);
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
    public function destroy(IssueStockDetail $issueStockDetail): JsonResponse
    {
        $item = $issueStockDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
