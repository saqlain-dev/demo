<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DisposeItem;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DisposeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['data'] = DisposeItem::query()->with('inventory','poDetail','item')->get();
        $data['data']->each( function ($record){
            $record->approval_request = getNextApproval(12,auth()->user()->designation_id,$record->id);
            $record->approval_request_status=checkApprovalRequestStatus(12,$record->id);
        });
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        $request->validate([
//            'financial_year' => 'required|integer',
//            'start_date' => 'required|date|before_or_equal:end_date|date_format:Y-m-d',
//            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d',
//        ]);
//
//        try {
//            DB::beginTransaction();
//
//            $disposeItem = DisposeItem::query()->create($request->all());
//
//            DB::commit();
//            return resp(1, 'Successful!', $disposeItem, Response::HTTP_CREATED);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
//        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DisposeItem $disposeItem)
    {
        $data['approval_request']=getNextApproval(12,auth()->user()->designation_id, $disposeItem->id);
        $data['approval_request_status'] = checkApprovalRequestStatus(12, $disposeItem->id);
        $data['dispose_item'] = $disposeItem->load('inventory','poDetail','item');
        return resp(1, 'Successful!', $data , Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DisposeItem $disposeItem)
    {
        $request->validate([
            'dispose_quantity' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $disposeItem->update($request->all());

            DB::commit();
            return resp(1, 'Successful!', $disposeItem, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DisposeItem $disposeItem)
    {
        $disposeItem->delete();
        return resp(1, 'Successful!', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['financial_years_list'] = Type::getTypeValues('financial-years');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
