<?php

namespace App\Http\Controllers\Api\V1\Admin\GDN;

use App\Http\Controllers\Controller;
use App\Models\Admin\GDN\Gdn;
use App\Models\Admin\GDN\GdnItem;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessList;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GdnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'date' => 'required|date',
                'po_id' => 'required|integer',
            ]);
            $statement = DB::select("SELECT IDENT_CURRENT('gdns') as nextID");
            $GdnNO='GDN/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['gdn_no']=$GdnNO;
            $this->input['vendor_id']=auth()->user()->vendor_id;
            $this->input['date']=date('Y-m-d',strtotime($request->date));

            $gdn=Gdn::query()->create($this->input);

            if($gdn){
                $poItemDetail=PurchaseOrderDetail::query()->where('purchase_order_id',$request->po_id)->get();
                foreach($poItemDetail as $poitems){
                    $itemInsert=array(
                        'required_quantity'=>$poitems->required_quantity,
                        'unit_of_measurement'=>$poitems->unit_of_measurement,
                        'unit_price'=>$poitems->unit_price,
                        'item_id'=>$poitems->item_id,
                        'gdn_id'=>$gdn->id,
                    );
                    $gdnitem=GdnItem::query()->create($itemInsert);
                }

            }
            DB::commit();

            return resp('1', 'GDN added Successfully!', $gdn->load('gdnItem.itemDetail'), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function addGdnItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'gdn_id' => 'required|integer',
                'po_item_id' => 'required|integer',
            ]);
            $poItemDetail=PurchaseRequestDetail::query()->findOrFail($request->po_item_id);
            unset($this->input['po_item_id']);
            $this->input['required_quantity']=$poItemDetail->required_quantity;
            $this->input['unit_of_measurement']=$poItemDetail->unit_of_measurement;
            $this->input['unit_price']=$poItemDetail->unit_price;
            $this->input['item_id']=$poItemDetail->item_id;

            $gdnitem=GdnItem::query()->create($this->input);
            DB::commit();
            return resp('1', 'GDN item added Successfully!', $gdnitem, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateGdnItem(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'gdn_item_id' => 'required|integer',
                'required_quantity' => 'required',
            ]);
            $update=array();
            $update['required_quantity']=$this->input['required_quantity'];
            if($this->input['gdn_description'] != ""){
                $update['gdn_description']=$this->input['gdn_description'];
            }

            $gdnitem=GdnItem::query()->where('id',$this->input['gdn_item_id'])->update($update);
            DB::commit();
            return resp('1', 'GDN item updated Successfully!', $gdnitem, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function sendGdnForApproval(Gdn $item)
    {
        $item->status=2;
        $item->save();
        return resp(1,'Successful!', $item,Response::HTTP_OK);

    }

    /**
     * Display the specified resource.
     */
    public function show(Gdn $gdn)
    {
        $data['view_gdn']=Gdn::query()->with('gdnItem.itemDetail','vendorDetail')->findOrFail($gdn->id);

        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gdn $gdn)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gdn $gdn)
    {
        $gdn->gdnItem()->delete();
        $gdn->delete();
        return resp(1,'GDN deleted Successful!', [],Response::HTTP_OK);
    }

    public function gdnApprovalList()
    {
        $statusValues = [1,2, 3];
        $data['gdn_list']=Gdn::query()->whereIn('status',$statusValues)->with('vendorDetail','poDetails')->get();
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }
    public function approveGdn(Request $request)
    {

        $gdn=Gdn::query()->findOrFail($request->gdn_id);
        $gdn->load('gdnItem','poDetails')->toArray();



        $gdn->status=$request->status;
        $gdn->rej_comments=$request->rej_comments;
        $gdn->save();
        $gdn->refresh();
        return resp(1,'Successful!', $gdn,Response::HTTP_OK);
    }


}
