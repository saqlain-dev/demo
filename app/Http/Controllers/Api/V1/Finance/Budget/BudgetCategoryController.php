<?php

namespace App\Http\Controllers\Api\V1\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\Admin\Location;
use App\Models\Finance\Budget\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BudgetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BudgetCategory::getBudgetCategory();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $item = BudgetCategory::query()->create($request->all());
        return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $item = BudgetCategory::query()->with('subCategory')->findOrFail($id);
        return resp('1', 'Successful!', $item, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BudgetCategory $budgetCategory)
    {
        $request->validate([
            'parent_id' => 'nullable|integer',
            'name' => 'required|max:255',
            'description' => 'required',
        ]);

        $budgetCategory->update($request->all());
        return resp('1', 'Record Created Successfully!', $budgetCategory->refresh(), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BudgetCategory $budgetCategory)
    {
        $budgetCategory->subCategory()->delete();
        $budgetCategory->delete();
        return resp('1', 'Record Deleted Successfully!', [], Response::HTTP_OK);
    }
}
