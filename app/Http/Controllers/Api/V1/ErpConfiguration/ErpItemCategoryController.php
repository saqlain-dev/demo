<?php

namespace App\Http\Controllers\Api\V1\ErpConfiguration;

use App\Http\Controllers\Controller;
use App\Models\ErpConfiguration\ErpItemCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ErpItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['category_list']=ErpItemCategory::query()->with('categoryDepartment')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_name' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $category=ErpItemCategory::query()->create( $this->input);


            DB::commit();
            return resp(1,'Successful!', $category,Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ErpItemCategory $erp_item_category)
    {
        return resp(1,'Successful!', $erp_item_category->load('categoryDepartment'),Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ErpItemCategory $erp_item_category)
    {
        $request->validate([
            'category_name' => 'required',
        ]);
        try {
            DB::beginTransaction();
            ErpItemCategory::query()->where('id',$erp_item_category->id)->update($this->input);
            $erp_item_category=ErpItemCategory::query()->findOrFail($erp_item_category->id);

            DB::commit();
            return resp(1,'Successful!', $erp_item_category->load('categoryDepartment'),Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ErpItemCategory $erp_item_category)
    {
        $erp_item_category->delete();
        $message='Item category deleted successfully.';
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }
}
