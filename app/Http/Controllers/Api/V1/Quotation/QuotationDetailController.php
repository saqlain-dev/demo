<?php

namespace App\Http\Controllers\Api\V1\Quotation;

use App\Http\Controllers\Controller;
use App\Models\Quotation\Quotation;
use App\Models\Quotation\QuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class QuotationDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            '*.quotation_id' => 'required|integer',       // Validate 'item_code' as required and string
            '*.rfp_item_id' => 'required|integer',       // Validate 'item_code' as required and string
            //'*.item_id' => 'required|integer',       // Validate 'item_code' as required and string
            //'*.item_code' => 'required|string',       // Validate 'item_code' as required and string
            //'*.erp_category_id' => 'required|integer',
            //'*.erp_sub_category_id' => 'required|integer',
            '*.item_name' => 'required|string',
            '*.item_quantity' => 'required|numeric', // Validate 'item_quantity' as required and numeric
            '*.uom' => 'required|integer',            // Validate 'uom' as required and string
            '*.rate' => 'required|numeric',          // Validate 'rate' as required and numeric
            '*.amount' => 'required|numeric',        // Validate 'amount' as required and numeric
        ]);

        try {
            DB::beginTransaction();
            $quotationId = collect($request->all())->pluck('quotation_id')->unique()->toArray();
            $createdById=auth()->user()->id;
            $this->input = array_map(function ($item) use ($createdById) {
                $item['created_by'] = $createdById;
                return $item;
            }, $this->input);
            $quotationDetail=QuotationDetail::query()->insert($this->input);

            $sum = QuotationDetail::where('quotation_id', $quotationId)->sum('amount');
            Quotation::query()->where('id',$quotationId)->update(array('quotation_amount'=>$sum));

            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(QuotationDetail $quotation_detail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QuotationDetail $quotation_detail)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QuotationDetail $quotation_detail)
    {
        $quotation_detail->delete();
        $sum = QuotationDetail::where('quotation_id', $quotation_detail->quotation_id)->sum('amount');
        Quotation::query()->where('id',$quotation_detail->quotation_id)->update(array('quotation_amount'=>$sum));
        return resp(1, 'Record deleted successfully!',[], Response::HTTP_OK);
    }
}
