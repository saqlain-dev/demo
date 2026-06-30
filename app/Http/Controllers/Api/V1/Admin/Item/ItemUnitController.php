<?php

namespace App\Http\Controllers\Api\V1\Admin\Item;

use App\Http\Controllers\Controller;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ItemUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        $unitList=ItemUnit::all();
        return resp(1,'Successful!', $unitList,Response::HTTP_CREATED);
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
            'unit_name' => 'required',
            'unit_symbol' => 'required',
        ]);
        $unit=ItemUnit::query()->create( $this->input);

        return resp(1,'Successful!', $unit,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(ItemUnit $itemUnit)
    {
        $this->authorizeAny([
            'item_category_view'
        ]);

        return resp(1,'Successful!', $itemUnit,Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ItemUnit $itemUnit)
    {
        $this->authorizeAny([
            'item_category_update'
        ]);

        $request->validate([
            'unit_name' => 'required',
            'unit_symbol' => 'required',
        ]);
        ItemUnit::query()->where('id',$itemUnit->id)->update( $this->input);
        $unit=ItemUnit::query()->findOrFail($itemUnit->id);
        return resp(1,'Successful!', $unit,Response::HTTP_CREATED);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ItemUnit $itemUnit)
    {
        $this->authorizeAny([
            'item_category_delete'
        ]);

        $itemUnit->delete();
        $message='Unit deleted successfully.';
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
