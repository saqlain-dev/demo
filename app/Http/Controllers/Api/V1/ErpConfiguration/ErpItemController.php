<?php

namespace App\Http\Controllers\Api\V1\ErpConfiguration;

use App\Http\Controllers\Controller;
use App\Models\ErpConfiguration\ErpItem;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ErpItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['item']=ErpItem::with(['subCategory','itemCategory','itemType'])->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required',
            'category_id' => 'required'
        ]);
        try {
            DB::beginTransaction();
            $statement = DB::select("SELECT IDENT_CURRENT('items') as nextID");
            $this->input['item_code']=sprintf('%05d', $statement[0]->nextID);
            $item=ErpItem::query()->create($this->input);
            $itemDetail=ErpItem::with(['subCategory','itemCategory','itemType'])->findOrFail($item->id);
            DB::commit();
            return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ErpItem $erp_item)
    {
        $itemDetail=$erp_item::with(['subCategory','itemCategory','itemType'])->findOrFail($erp_item->id);
        return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ErpItem $erp_item)
    {
        $request->validate([
            'item_name' => 'required',
            'category_id' => 'required'
        ]);
        try {
            DB::beginTransaction();
            ErpItem::query()->where('id',$erp_item->id)->update($this->input);
            $itemDetail=ErpItem::with(['subCategory','itemCategory','itemType'])->findOrFail($erp_item->id);
            DB::commit();
            return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ErpItem $erp_item)
    {
        $erp_item->delete();
        $message="Item deleted successfully";
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }

    public function getItemDropDown()
    {

        $data['categories']=ErpItemCategory::with('itemSubcategory')->get();
        $data['item_type']=Type::getTypeValues('erp-item-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
