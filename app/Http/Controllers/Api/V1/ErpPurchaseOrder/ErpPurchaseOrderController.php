<?php

namespace App\Http\Controllers\Api\V1\ErpPurchaseOrder;

use App\Http\Controllers\Controller;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\ErpPurchaseOrder\ErpPurchaseOrder;
use App\Models\ErpPurchaseOrder\ErpPurchaseOrderItem;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ErpPurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'crm_po_view',
        ]);
        $data['purchase_order_list']=ErpPurchaseOrder::query()->with('purchaseOrderDetail.item','purchaseOrderDetail.uom','quotation')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'crm_po_create',
        ]);
        $request->validate([
            'quotation_id' => 'required',
            'purchase_date' => 'required|date_format:Y-m-d',
            'quotation_no' => 'required',
            'po_date' => 'required|date_format:Y-m-d',
            'ship_to_address' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            if($request->hasFile('po_attachment')) {

                $responce = $this->saveAttachment($request, 'PurchaseOrderAttachment');

                if ($responce) {
                    $this->input['po_attachment'] = $responce;
                }
            }
            $purchaseOrder=ErpPurchaseOrder::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $purchaseOrder, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveAttachment($request,$folder){

        $file = $request->file('po_attachment');
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

    public function addErpPoItems(Request $request)
    {

        $request->validate([
            '*.purchase_order_id' => 'required|integer',
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

            $erpPurchaseOrderItem=ErpPurchaseOrderItem::class::query()->insert($this->input);

            DB::commit();
            return resp(1, 'Successful!', $erpPurchaseOrderItem, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ErpPurchaseOrder $erp_purchase_order)
    {
        $this->authorizeAny([
            'crm_po_view',
        ]);
        $data['purchase_order']=$erp_purchase_order->load('purchaseOrderDetail.item','purchaseOrderDetail.uom','quotation.quotationDetail');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ErpPurchaseOrder $erp_purchase_order)
    {
        $this->authorizeAny([
            'crm_po_update',
        ]);
        $request->validate([
            'quotation_id' => 'required',
            'purchase_date' => 'required|date_format:Y-m-d',
            'quotation_no' => 'required',
            'po_date' => 'required|date_format:Y-m-d',
            'ship_to_address' => 'required|string',
        ]);

        try {
            DB::beginTransaction();
            if($request->hasFile('po_attachment')) {

                $responce = $this->saveAttachment($request, 'PurchaseOrderAttachment');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }
            $erp_purchase_order->update($this->input);
            $erp_purchase_order->refresh();

            DB::commit();
            return resp(1, 'Successful!', $erp_purchase_order, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ErpPurchaseOrder $erp_purchase_order)
    {
        $this->authorizeAny([
            'crm_po_delete',
        ]);
        $erp_purchase_order->purchaseOrderDetail()->delete();
        $erp_purchase_order->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getErpPoDropDown()
    {
        $data['uom']=Type::getTypeValues('uom');
        $data['item_category_list']=ErpItemCategory::query()->with('itemSubcategory')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function deleteErpPoItem(Request $request)
    {
        $request->validate([
            'erp_po_item_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $erp_po_item=$request->erp_po_item_id;
            $erp_poItem=ErpPurchaseOrderItem::query()->find($erp_po_item);
            if($erp_poItem){
                $erp_poItem->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
}
