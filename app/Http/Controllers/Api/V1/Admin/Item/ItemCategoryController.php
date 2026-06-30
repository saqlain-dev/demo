<?php

namespace App\Http\Controllers\Api\V1\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $categoryList=ItemCategory::all();
        return resp(1,'Successful!', $categoryList,Response::HTTP_CREATED);
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
            'category_name' => 'required',
        ]);
        $category=ItemCategory::query()->create( $this->input);

        return resp(1,'Successful!', $category,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemCategory $itemCategory)
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        return resp(1,'Successful!', $itemCategory,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory)
    {
        $this->authorizeAny([
            'item_category_update'
        ]);

        $request->validate([
            'category_name' => 'required',
        ]);
        ItemCategory::query()->where('id',$itemCategory->id)->update($this->input);
        $itemCategory=ItemCategory::query()->findOrFail($itemCategory->id);
        return resp(1,'Successful!', $itemCategory,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemCategory $itemCategory)
    {
        $this->authorizeAny([
            'item_category_delete'
        ]);

        $itemCategory->delete();
        $message='Item category deleted successfully.';
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
