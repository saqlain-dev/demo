<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\IssueStock;
use App\Models\Admin\ItemVariant;
use App\Models\Admin\StockRequest;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class IssueStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = IssueStock::query()->with(['StockRequestId','IssueStockDetail','created_by','updated_by'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'stock_request_id' => 'required',
            'issue_date' => 'required',
            'status' => 'required',
            'issue_stock_detail.*.item_id' => 'required',
            'issue_stock_detail.*.qty' => 'required',
            //'issue_stock_detail.*.remarks' => 'required',
            //'issue_stock_detail.*.policy_document' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = IssueStock::query()->create($request->only('stock_request_id', 'issue_date', 'status'));
            if ($item){

                $childData = array_map(function ($detail) use ($item) {
                    $varient="";

                    $Stock_request=StockRequest::query()->where('id',$item->stock_request_id)->first();
                    if (Arr::exists($detail, 'variant') && is_array($detail['variant']) && !empty($detail['variant'])) {

                        $varient=json_encode($detail['variant']);
                        ItemVariant::query()->whereIn('id',$detail['variant'])->update(array('assign_to_dept'=>$Stock_request->department_id));
                    }
                    return [
                        'issue_stock_id' => $item->id, // Assign the parent ID to each child record
                        'item_id' => $detail['item_id'],
                        'qty' => $detail['qty'],
                        'remarks' => $detail['remarks'],
                        'variant_ids' =>$varient,
                        //'policy_document' => $detail['policy_document'],
                    ];

                }, $request->input('issue_stock_detail'));

                $item->IssueStockDetail()->createMany($childData);
            }
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
    public function show(IssueStock $issueStock): JsonResponse
    {
        $logBook = $issueStock->load(['StockRequestId','IssueStockDetail','created_by','updated_by']);
        foreach ($logBook->IssueStockDetail as $key => $issueDetail) {
            $variants = $issueDetail->variantsDetail();
            $logBook->IssueStockDetail[$key]['variants']=$variants;
        }
        return resp('1', 'Successful!', $logBook, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IssueStock $issueStock)
    {
        $request->validate([
            'stock_request_id' => 'required',
            'issue_date' => 'required',
            'status' => 'required',
            'issue_stock_detail.*.item_id' => 'required',
            'issue_stock_detail.*.qty' => 'required',
            //'issue_stock_detail.*.remarks' => 'required',
            //'issue_stock_detail.*.policy_document' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $issueStock->update($request->only('stock_request_id', 'issue_date', 'status'));
            // Prepare the child data
            $childData = array_map(function ($detail) use ($issueStock) {
                return [
                    'issue_stock_id' => $issueStock->id, // Assign the parent ID to each child record
                    'item_id' => $detail['item_id'],
                    'qty' => $detail['qty'],
                    'remarks' => $detail['remarks'],
                    //'policy_document' => $detail['policy_document'],
                ];
            }, $request->input('issue_stock_detail'));

            // Delete existing child records
            $issueStock->IssueStockDetail()->delete();

            // Create the updated child records
            $issueStock->IssueStockDetail()->createMany($childData);
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
    public function destroy(IssueStock $issueStock): JsonResponse
    {
        $issueStock->IssueStockDetail()->delete();
        $item = $issueStock->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function getItemVariantsByItemId(Request $request)
    {
        $request->validate([
            'itemIds' => 'required|array',
        ]);
        //$item = Item::query()->with('itemVariants')->findOrFail($itemId);

        // Retrieve items with their variants
        $itemIds = $request->input('itemIds');
        $items = Item::query()
            ->with('itemVariants.item')
            ->whereIn('id', $itemIds)
            ->get();
        // Format the data

        $formattedItems = $items->map(function ($item) {
            // Filter itemVariants based on the conditions
            $filteredVariants = $item->itemVariants->filter(function ($variant) {
                return is_null($variant->assign_to_dept) && is_null($variant->assign_to_emp) && ($variant->inventory_type == 0);
            });
            return [
                'id' => $item->id,
                'name' => $item->name,
                'itemVariants' => $filteredVariants->values()->toArray() // Ensure keys are reindexed
            ];
        })->toArray();
        return resp('1', 'Successful!', $formattedItems, Response::HTTP_OK);
    }
}
