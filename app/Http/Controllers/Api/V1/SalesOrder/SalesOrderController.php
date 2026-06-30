<?php

namespace App\Http\Controllers\Api\V1\SalesOrder;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\SalesOrder\SalesOrder;
use App\Models\SalesOrder\SalesOrderItem;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['sales_order']=SalesOrder::query()->with(['orderType','salesOrderItems'=>['item','uom'],'customer','purchaseOrder'=>['purchaseOrderDetail.item','purchaseOrderDetail.uom']])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'po_id' => 'required|integer',
            'sales_order_series' => 'required|string',
            'customer_id' => 'required|integer',
            'delivery_date' => 'required|date_format:Y-m-d',
            'order_type' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('sales_orders') as nextID");
            $so_series_number='SO-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['sales_order_series']=$so_series_number;
            $sales_order=SalesOrder::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $sales_order, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesOrder $sales_order)
    {
        return resp(1, 'Successful!', $sales_order->load(['orderType','salesOrderItems'=>['item','uom'],'customer','purchaseOrder'=>['purchaseOrderDetail.item','purchaseOrderDetail.uom']]), Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesOrder $sales_order)
    {
        $request->validate([
            'po_id' => 'required|integer',
            'sales_order_series' => 'required|string',
            'customer_id' => 'required|integer',
            'delivery_date' => 'required|date_format:Y-m-d',
            'order_type' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $sales_order->update($this->input);
            $sales_order->refresh();

            DB::commit();
            return resp(1, 'Successful!', $sales_order->load(['orderType','salesOrderItems'=>['item','uom'],'customer','purchaseOrder'=>['purchaseOrderDetail.item','purchaseOrderDetail.uom']]), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesOrder $sales_order)
    {
        $sales_order->salesOrderItems()->delete();
        $sales_order->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getSalesOrderDropDown()
    {
        $data['customers']=Customer::query()->get();
        $data['order_type']=Type::getTypeValues('sales-order-type');
        $data['item_category_list']=ErpItemCategory::query()->with('itemSubcategory')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addSalesOrderItems(Request $request)
    {
        $request->validate([
            '*.sales_order_id' => 'required|integer',
            //'*.item_id' => 'required|integer',
            //'*.erp_category_id' => 'required|integer',
            //'*.erp_sub_category_id' => 'required|integer',
            '*.item_name' => 'required|string',
            '*.item_quantity' => 'required|numeric',
            '*.uom' => 'required|integer',
            '*.rate' => 'required|numeric',
            '*.amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $createdById = auth()->user()->id;
            $this->input = array_map(function ($item) use ($createdById) {
                $item['created_by'] = $createdById;
                return $item;
            }, $this->input);

            $salesOrderItem=SalesOrderItem::class::query()->insert($this->input);

            DB::commit();
            return resp(1, 'Successful!', $salesOrderItem, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteSalesOrderItem(Request $request)
    {
        $request->validate([
            'sales_order_item_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $sales_order_item=$request->sales_order_item_id;
            $saleOrderItem=SalesOrderItem::query()->find($sales_order_item);
            if($saleOrderItem){
                $saleOrderItem->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
}
