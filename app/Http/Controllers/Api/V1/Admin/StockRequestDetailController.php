<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\RfqType;
use App\Models\Admin\StockRequest;
use App\Models\Admin\StockRequestDetail;
use App\Models\BranchOffice;
use App\Models\District;
use App\Models\Employee;
use App\Models\Finance\Budget\ProjectBudget;
use App\Models\HeadOffice;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = StockRequestDetail::query()->with(['StockRequestId','ItemCategoryId'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_request_id' => 'required',
            'item_category_id' => 'required',
            'qty' => 'required',
            'remarks' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = StockRequestDetail::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockRequestDetail $stockRequestDetail): JsonResponse
    {
        $logBook = $stockRequestDetail->load(['StockRequestId','ItemCategoryId','created_by','updated_by']);
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockRequestDetail $stockRequestDetail)
    {
        $request->validate([
            'stock_request_id' => 'required',
            'item_category_id' => 'required',
            'qty' => 'required',
            'remarks' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $stockRequestDetail->update($this->input);
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
    public function destroy(StockRequestDetail $stockRequestDetail): JsonResponse
    {
        $item = $stockRequestDetail->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getDropDown()
    {

        $data['departments'] = Type::getTypeValues('department-names');
        $data['hOffices'] = BranchOffice::all();
        $data['employees'] = Employee::all();
        $data['items'] = Item::all();
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }
}
