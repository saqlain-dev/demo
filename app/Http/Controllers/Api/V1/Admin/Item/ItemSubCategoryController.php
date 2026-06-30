<?php

namespace App\Http\Controllers\Api\V1\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemSubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $itemSubCategoryList=ItemSubCategory::with('itemCategory')->get();
        return resp(1,'Successful!', $itemSubCategoryList,Response::HTTP_CREATED);
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
            'category_id' => 'required',
            'sub_category_name' => 'required',
        ]);
        $itemSubCategory=ItemSubCategory::query()->create($this->input);
        $itemSubCategory=ItemSubCategory::with('itemCategory')->findOrFail($itemSubCategory->id);
        return resp(1,'Successful!', $itemSubCategory,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemSubCategory $itemSubCategory)
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $itemSubCategory=ItemSubCategory::with('itemCategory')->findOrFail($itemSubCategory->id);
        return resp(1,'Successful!', $itemSubCategory,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemSubCategory $itemSubCategory)
    {
        $this->authorizeAny([
            'item_category_update'
        ]);

        $request->validate([
            'category_id' => 'required',
            'sub_category_name' => 'required',
        ]);
       ItemSubCategory::query()->where('id',$itemSubCategory->id)->update($this->input);
        $itemSubCategory=ItemSubCategory::with('itemCategory')->findOrFail($itemSubCategory->id);
        return resp(1,'Successful!', $itemSubCategory,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemSubCategory $itemSubCategory)
    {
        $this->authorizeAny([
            'item_category_delete'
        ]);

        $itemSubCategory->delete();
        $message='Item sub-category deleted successfully';
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
