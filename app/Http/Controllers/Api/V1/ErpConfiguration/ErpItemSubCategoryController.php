<?php

namespace App\Http\Controllers\Api\V1\ErpConfiguration;

use App\Http\Controllers\Controller;
use App\Models\ErpConfiguration\ErpItemCategory;
use App\Models\ErpConfiguration\ErpItemSubCategory;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ErpItemSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['item_sub_category_list']=ErpItemSubCategory::with('itemCategory')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'sub_category_name' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item_sub_category=ErpItemSubCategory::query()->create( $this->input);


            DB::commit();
            return resp(1,'Successful!', $item_sub_category->load('itemCategory'),Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ErpItemSubCategory $erp_item_sub_category)
    {
        $erp_item_sub_category=ErpItemSubCategory::with('itemCategory')->findOrFail($erp_item_sub_category->id);
        return resp(1,'Successful!', $erp_item_sub_category->load('itemCategory'),Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ErpItemSubCategory $erp_item_sub_category)
    {
        $request->validate([
            'category_id' => 'required',
            'sub_category_name' => 'required',
        ]);
        try {
            DB::beginTransaction();
            ErpItemSubCategory::query()->where('id',$erp_item_sub_category->id)->update($this->input);
            $erp_item_sub_category=ErpItemSubCategory::with('itemCategory')->findOrFail($erp_item_sub_category->id);

            DB::commit();
            return resp(1,'Successful!', $erp_item_sub_category->load('itemCategory'),Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ErpItemSubCategory $erp_item_sub_category)
    {
        $erp_item_sub_category->delete();
        $message='Item sub-category deleted successfully';
        return resp(1,'Successful!', $message,Response::HTTP_CREATED);
    }

    public function getCategoryDropDown()
    {
        $data['item_category_list']=ErpItemCategory::all();
        $data['department']= Type::getTypeValues('department-names');
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }
}
