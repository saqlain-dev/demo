<?php

namespace App\Http\Controllers\Api\V1\RFP;

use App\Http\Controllers\Controller;
use App\Models\Division\Division;
use App\Models\ErpConfiguration\ErpItem;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\Lead;
use App\Models\Opportunity\Opportunity;
use App\Models\RFP\Rfp;
use App\Models\RFP\RfpItem;
use App\Models\SalesTeam\SalesTeam;
use App\Models\Supplier\Supplier;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class RfpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'crm_rfp_view',
        ]);
        $data['rfp_list']=Rfp::query()->with(['rfpStatus','opportunity','rfpDetail'=>['item','uom','division','brand']])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'crm_rfp_create',
        ]);
        $request->validate([
            //'rfp_series' => 'required',
            'rfp_status' => 'required|integer',
            'opportunity_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'valid_till' => 'required|date_format:Y-m-d',
            //'items*.item_id' => 'required|integer',
            //'items*.item_quantity' => 'required|numeric',
            //'items*.uom' => 'required|integer',
            //'items*.rate' => 'required|numeric',
            //'items*.amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();
            $rfpItems=$request->items;
            $statement = DB::select("SELECT IDENT_CURRENT('rfps') as nextID");
            $rfp_series_number='RFP-'.date('Y').'-'.sprintf('%04d', $statement[0]->nextID);
            $this->input['rfp_series']=$rfp_series_number;
            $rfp=Rfp::query()->create($this->input);

            /*if($rfp && $rfpItems){
                foreach($rfpItems as $key => $items){

                    $rfpItem=array(
                        'item_id'=>$items['item_id'],
                        'item_quantity'=>$items['item_quantity'],
                        'uom'=>$items['uom'],
                        'rate'=>$items['rate'],
                        'amount'=>$items['amount'],
                        'rfp_id'=>$rfp->id
                    );


                    $rfpItems=RfpItem::query()->insert($rfpItem);
                }
            }*/

            DB::commit();
            return resp(1, 'Successful!', $rfp->load(['rfpStatus','opportunity','rfpDetail'=>['item','uom']]), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Rfp $rfp)
    {
        $this->authorizeAny([
            'crm_rfp_view',
        ]);
        $data['rfp']=$rfp->load(['rfpStatus','opportunity','rfpDetail'=>['item','uom','division','brand','assignToEmployee'],'rfq' => ['rfqStatus','rfqDetail.uom','rfqDetail.item']]);
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rfp $rfp)
    {
        $this->authorizeAny([
            'crm_rfp_update',
        ]);
        $request->validate([
            //'rfp_series' => 'required',
            'rfp_status' => 'required|integer',
            'opportunity_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'valid_till' => 'required|date_format:Y-m-d',
        ]);

        try {
            DB::beginTransaction();
            $rfp->update($this->input);
            $rfp->refresh();

            DB::commit();
            return resp(1, 'Successful!', $rfp->load('rfpStatus','opportunity','rfpDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rfp $rfp)
    {
        $this->authorizeAny([
            'crm_rfp_delete',
        ]);
        $rfp->rfpDetail()->delete();
        $rfp->delete();
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }

    public function getRfpDropDown()
    {

        $data['brand_name']=Type::getTypeValues('brand-name');
        $data['uom']=Type::getTypeValues('uom');
        $data['rfp_status']=Type::getTypeValues('rfp-status');
        $data['opportunity_list']=Opportunity::query()->get();
        $data['erp_items']=ErpItem::with(['subCategory','itemCategory','itemType'])->get();
        $data['item_category_list']=ErpItemCategory::query()->with('itemSubcategory')->get();
        $data['divisions']=Division::query()->with('divisionEmployee')->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    // RFP Items

    public function addRfpItems(Request $request)
    {
        $request->validate([
            '*.rfp_item_id' => 'nullable|integer', // Only required for updates
            '*.rfp_id' => 'required|integer',
            '*.division_id' => 'required|integer',
           // '*.item_id' => 'required|integer',
           // '*.erp_category_id' => 'required|integer',
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
            foreach ($request->all() as $item) {
                $item['created_by'] = $createdById;

                if(isset($item['rfp_item_id'])){
                    $rfp_item_id=$item['rfp_item_id'];
                    unset($item['rfp_item_id']);
                    RfpItem::where('id', $rfp_item_id)->update($item);
                }else{
                    RfpItem::query()->insert($item);
                }
                /*$rfp_item_id=$item['rfp_item_id'];
                unset($item['rfp_item_id']);
                if (!empty($rfp_item_id)) {
                    // Update existing record
                    RfpItem::where('id', $rfp_item_id)->update($item);
                } else {
                    // Insert new record
                    RfpItem::query()->insert($item);
                }*/
            }

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function deleteRfpItem(Request $request)
    {
        $request->validate([
            'rfp_item_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $rfp_item_id=$request->rfp_item_id;
            $rfp_item=RfpItem::query()->find($rfp_item_id);
            if($rfp_item){
                $rfp_item->delete();
            }

            DB::commit();
            return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    public function sendForPricing(Request $request)
    {
        $request->validate([
            'rfp_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            $rfp = Rfp::find($request->rfp_id);
            if ($rfp) {
                $rfp->update(['rfp_status' => 667]);

                RfpItem::where('rfp_id', $rfp->id)->update(['item_status' => 2]);
                Opportunity::where('id', $rfp->opportunity_id)->update(['opportunity_status' => 649]);
                $divisionHeads = Division::pluck('division_head_id')->unique()->toArray();
                $opportunity=Opportunity::query()->where('id', $rfp->opportunity_id)->first();
                $opportunity_name = !empty($opportunity->opportunity_name) ? $opportunity->opportunity_name : "New Opportunity";
                $title="RFP Sent for Pricing";
                $message="The RFP ".$opportunity_name." has been sent for pricing. Please review and proceed accordingly.";

                sendWebNotification($divisionHeads,$title,$message,'sent-for-pricing');
            }


            DB::commit();
            return resp(1, 'successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function submitPricing(Request $request)
    {
        $request->validate([
            'rfp_id' => 'required|integer',
            'opportunity_id' => 'required|integer',
            'items' => 'required|array',
            'items.*.item_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->items as $item) {

                DB::table('rfp_items') // Replace with actual table name
                ->where('id', $item['item_id'])
                    ->update([
                        'item_status' => 1,
                    ]);
            }
            $rfp_items=RfpItem::query()->where('rfp_id',$request->rfp_id)->where('item_status', '!=', 1)->count();

            if ($rfp_items == 0) {
                Rfp::query()->where('id',$request->rfp_id)->update(['rfp_status'=>668]);
                Opportunity::query()->where('id',$request->rfp_id)->update(['opportunity_status'=>650]);

            }
            $submitPricingOwner=auth()->user()->employee_id;
            $salesHeads = SalesTeam::whereNotNull('sales_head_id')->pluck('sales_head_id')->unique()->toArray();

            $salesHeadsArray = is_array($salesHeads) ? $salesHeads : [$salesHeads];
            $salesOwnerArray = is_array($submitPricingOwner) ? $submitPricingOwner : [$submitPricingOwner];
            $salesHeads = array_unique(array_merge($salesOwnerArray, $salesHeads));
            $title="Pricing Received! ";
            $message="Pricing have been finalized and submitted.";

            sendWebNotification($salesHeads,$title,$message,'pricing-submission');


            DB::commit();
            return resp(1, 'successfully!',[], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}
