<?php

namespace App\Http\Controllers\Api\V1\RFQ;

use App\Http\Controllers\Controller;
use App\Models\ErpConfiguration\ErpItem;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\Opportunity\Opportunity;
use App\Models\RFP\Rfp;
use App\Models\RFP\RfpItem;
use App\Models\RFQ\Rfq;
use App\Models\RFQ\RfqItem;
use App\Models\Supplier\Supplier;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RfqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'crm_rfq_view',
        ]);
        $data['rfq_list']=Rfq::query()->with('rfqStatus','rfqDetail','rfp','supplier.supplierGroup','supplier.supplierType')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'crm_rfq_create',
        ]);
        $request->validate([
            //'rfq_series' => 'required',
            'rfq_status' => 'required|integer',
            'rfp_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            //'items' => 'required|array|min:1', // Ensures at least one item
            'items.*.item_name' => 'required|string',
            'items.*.item_quantity' => 'required|numeric',
            'items.*.uom' => 'required|integer',
            'items.*.rate' => 'required|numeric',
            'items.*.amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $rfqItems=$request->items;
            $statement = DB::select("SELECT IDENT_CURRENT('rfqs') as nextID");
            $rfq_series_number='RFQ-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['rfq_series']=$rfq_series_number;

            if($request->hasFile('attachment')) {

                $responce = $this->saveFile($request, 'RFQFiles');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }else{
                unset($this->input['attachment']);
            }
            $rfq=Rfq::query()->create($this->input);
            if($rfq && $rfqItems){
                foreach($rfqItems as $key => $items){

                    if(!empty($items['rfp_item_id'])){
                        $rfqItem=array(
                            'item_name'=>$items['item_name'],
                            'item_quantity'=>$items['item_quantity'],
                            'uom'=>$items['uom'],
                            'rate'=>$items['rate'],
                            'amount'=>$items['amount'],
                            'rfq_id'=>$rfq->id,
                            'rfp_item_id'=>$items['rfp_item_id'],
                        );

                        unset($items['division_id']);
                        $rfqItems=RfqItem::query()->insert($rfqItem);
                    }else{
                        $rfpItem=array(
                            'item_name'=>$items['item_name'],
                            'item_quantity'=>$items['item_quantity'],
                            'uom'=>$items['uom'],
                            'rate'=>$items['rate'],
                            'amount'=>$items['amount'],
                            'rfp_id'=>$request->rfp_id,
                            'division_id'=>$items['division_id'] ?? null,
                            'brand_id'=>$items['brand_id'] ?? null,
                            'erp_category_id'=>$items['erp_category_id'] ?? null,
                            'item_type_status'=>1,
                        );


                        $rfpItems=RfpItem::query()->create($rfpItem);
                        if($rfpItems){
                            $rfqItem=array(
                                'item_name'=>$items['item_name'],
                                'item_quantity'=>$items['item_quantity'],
                                'uom'=>$items['uom'],
                                'rate'=>$items['rate'],
                                'amount'=>$items['amount'],
                                'rfq_id'=>$rfq->id,
                                'rfp_item_id'=>$rfpItems->id,
                            );
                        $rfqItems=RfqItem::query()->insert($rfqItem);
                        }
                    }


                }
            }

            DB::commit();
            return resp(1, 'Successful!', $rfq->load('rfqStatus','rfp.rfpStatus','rfqDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveFile($request,$folder){

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
    public function show(Rfq $rfq)
    {
        $this->authorizeAny([
            'crm_rfq_view',
        ]);
        $data['rfq']=$rfq->load('rfqStatus','attachments','rfqDetail.item','rfqDetail.uom','rfp.rfpStatus','rfp.rfpDetail.division','rfp.rfpDetail.erpItemCategory','supplier.supplierGroup','supplier.supplierType','comments.createdBy');
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rfq $rfq)
    {
        $this->authorizeAny([
            'crm_rfq_update',
        ]);
        $request->validate([
            //'rfq_series' => 'required',
            'rfq_status' => 'required|integer',
            'rfp_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            if($request->hasFile('attachment')) {

                $responce = $this->saveFile($request, 'RFQFiles');

                if ($responce) {
                    $this->input['attachment'] = $responce;
                }
            }else{
                unset($this->input['attachment']);
            }
            $rfq->update($this->input);
            $rfq->refresh();

            DB::commit();
            return resp(1, 'Successful!', $rfq->load('rfqStatus','rfp.rfpStatus','rfqDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rfq $rfq)
    {
        $this->authorizeAny([
            'crm_rfq_delete',
        ]);
        $rfq->rfqDetail()->delete();
        $rfq->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getRfqDropDown()
    {

        $data['uom']=Type::getTypeValues('uom');
        $data['rfq_status']=Type::getTypeValues('rfq-status');
        $data['suppliers']=Supplier::query()->with('supplierType')->get();
        $data['rfp_list']=Rfp::query()->with(['rfpStatus','opportunity','rfpDetail'=>['item','uom']])->get();
        $data['item_category_list']=ErpItemCategory::query()->with('itemSubcategory')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    public function addRfqItems(Request $request)
    {
        $request->validate([
            '*.rfq_item_id' => 'nullable|integer',
            '*.rfq_id' => 'required|integer',
           // '*.rfp_item_id' => 'required|integer',
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
            /*$this->input = array_map(function ($item) use ($createdById) {
                $item['created_by'] = $createdById;
                return $item;
            }, $this->input);*/

            foreach ($request->all() as $item) {
                $item['created_by'] = $createdById;
                if(isset($item['rfq_item_id'])){
                    $rfq_item_id=$item['rfq_item_id'];
                    unset($item['rfq_item_id']);
                    $this->updateItemPrice($item);
                    RfqItem::where('id', $rfq_item_id)->update($item);
                }else{
                    if(!empty($item['rfp_item_id'])){
                        RfqItem::query()->insert($item);
                    }else{
                        $rfqDetail=Rfq::query()->find($item['rfq_id']);
                        if($rfqDetail) {
                            $rfpItem = array(
                                'item_name' => $item['item_name'],
                                'item_quantity' => $item['item_quantity'],
                                'uom' => $item['uom'],
                                'rate' => $item['rate'],
                                'amount' => $item['amount'],
                                'rfp_id' => $rfqDetail->rfp_id,
                                'division_id' => $items['division_id'] ?? null,
                                'brand_id' => $item['brand_id'] ?? null,
                                'erp_category_id' => $item['erp_category_id'] ?? null,
                                'item_type_status' => 1,
                            );


                            $rfpItems = RfpItem::query()->create($rfpItem);
                            if ($rfpItems) {
                                $rfqItem = array(
                                    'item_name' => $item['item_name'],
                                    'item_quantity' => $item['item_quantity'],
                                    'uom' => $item['uom'],
                                    'rate' => $item['rate'],
                                    'amount' => $item['amount'],
                                    'rfq_id' => $rfqDetail->id,
                                    'rfp_item_id' => $rfpItems->id,
                                );
                                $rfqItems = RfqItem::query()->insert($rfqItem);
                            }
                        }
                    }

                    //RfqItem::query()->insert($item);
                }


            }
            //$rfqItems=RfqItem::query()->insert($this->input);

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function updateItemPrice($item)
    {
        $rfp_item_id=$item['rfp_item_id'];
        if($rfp_item_id && $item['rate'] > 0){
        $amount=$item['rate'] * $item['item_quantity'];
            $update=array(
            'rate'=>$item['rate'],
            'amount'=>$amount,
        );
        RfpItem::query()->where('id',$rfp_item_id)->update($update);
        }
    }

    public function deleteRfqItem(Request $request)
    {
        $request->validate([
            'rfq_item_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $rfq_item_id=$request->rfq_item_id;
            $rfq_item=RfqItem::query()->find($rfq_item_id);
            if($rfq_item){
                $rfq_item->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }
}
