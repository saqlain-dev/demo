<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceDeductionResource;
use App\Models\Communication\EventSubCategory;
use App\Models\Configuration\AllowanceDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = EventSubCategory::query()->with('category')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:event_categories,id',
            'name' => 'required|string|max:255',
        ]);
        $item = EventSubCategory::query()->create($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(EventSubCategory $eventSubCategory)
    {
        $eventSubCategory->load('category');
        return resp('1', 'Successful!', $eventSubCategory, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventSubCategory $eventSubCategory)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:event_categories,id',
            'name' => 'required|string|max:255',
        ]);
        $item = $eventSubCategory->update($request->all());
        return resp('1', 'Successful!', $eventSubCategory, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventSubCategory $eventSubCategory)
    {
        $eventSubCategory->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
