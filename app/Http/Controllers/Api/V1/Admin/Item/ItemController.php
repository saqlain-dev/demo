<?php

namespace App\Http\Controllers\Api\V1\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\Type;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $itemDetail=Item::with(['itemType','itemUnit','createdUser','subCategory','itemCategory','itemVariants'])->get();
        return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
    }
    public function addItem(){

        $data['categories']=ItemCategory::with('itemSubcategory')->get();
        $data['unit']=ItemUnit::all();
        $data['item_type']=Type::getTypeValues('item-type');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'item_category_create'
        ]);

        $request->validate([
            'item_name' => 'required',
            'item_type' => 'required',
            'category_id' => 'required',
            'item_par_level' => 'required',
            'item_panic_level' => 'required',
            'item_reorder_qty' => 'required',
        ]);
        $statement = DB::select("SELECT IDENT_CURRENT('items') as nextID");
        $this->input['item_code']=sprintf('%05d', $statement[0]->nextID);
        $item=Item::query()->create($this->input);
        $itemDetail=Item::with(['itemType','itemUnit','createdUser','subCategory','itemCategory'])->findOrFail($item->id);
        return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $itemDetail=Item::with(['itemType','itemUnit','createdUser','subCategory','itemCategory','itemVariants'])->findOrFail($item->id);
        return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $this->authorizeAny([
            'item_category_update'
        ]);

        $request->validate([
            'item_name' => 'required',
            'item_type' => 'required',
            'category_id' => 'required',
            'item_par_level' => 'required',
            'item_panic_level' => 'required',
            'item_reorder_qty' => 'required',
        ]);
        Item::query()->where('id',$item->id)->update($this->input);
        $itemDetail=Item::with(['itemType','itemUnit','createdUser','subCategory','itemCategory'])->findOrFail($item->id);
        return resp(1,'Successful!', $itemDetail,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $this->authorizeAny([
            'item_category_delete'
        ]);

        $item->delete();
        $message="Item deleted successfully";
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
