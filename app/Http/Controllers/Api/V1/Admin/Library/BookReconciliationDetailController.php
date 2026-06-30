<?php

namespace App\Http\Controllers\Api\V1\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\Admin\Library\BookReconciliation;
use App\Models\Admin\Library\BookReconciliationDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookReconciliationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['BookReconciliationDetail'] = BookReconciliationDetail::with('BookReconciliationId','BookId','created_by','updated_by')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_reconciliation_id' => 'required',
            'book_id' => 'required',
            'actual_qty' => 'required',
            'difference' => 'required',
            //'remarks' => 'required',
            'date' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = BookReconciliationDetail::query()->create($this->input);
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
    public function show(BookReconciliationDetail $books_reconciliation_detail): JsonResponse
    {
        $book = $books_reconciliation_detail->load('BookReconciliationId','BookId','created_by','updated_by');
        return resp('1', 'Successful!', $book, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BookReconciliationDetail $books_reconciliation_detail)
    {
        $request->validate([
            'book_reconciliation_id' => 'required',
            'book_id' => 'required',
            'actual_qty' => 'required',
            'difference' => 'required',
            //'remarks' => 'required',
            'date' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $item = $books_reconciliation_detail->update($this->input);
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
    public function destroy(BookReconciliationDetail $books_reconciliation_detail): JsonResponse
    {

        $item = $books_reconciliation_detail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
