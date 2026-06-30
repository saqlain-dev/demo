<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Resources\AllowanceDeductionResource;
use App\Models\Communication\EventCategory;
use App\Models\Configuration\AllowanceDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('event_category_view');

        $data['listing'] = EventCategory::with('subCategories')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('event_category_create');

        $request->validate(['name' => 'required|string|max:255']);
        $item = EventCategory::query()->create($request->all());
        return resp('1', 'Successful!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(EventCategory $eventCategory)
    {
        $this->authorize('event_category_view');

        $eventCategory->load('subCategories');
        return resp('1', 'Successful!', $eventCategory, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EventCategory $eventCategory)
    {
        $this->authorize('event_category_update');

        $request->validate(['name' => 'required|string|max:255']);
        $item = $eventCategory->update($request->all());
        return resp('1', 'Successful!', $eventCategory, Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventCategory $eventCategory)
    {
        $this->authorize('event_category_delete');

        $eventCategory->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
