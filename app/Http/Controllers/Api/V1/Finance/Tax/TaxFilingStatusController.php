<?php

namespace App\Http\Controllers\Api\V1\Finance\Tax;

use App\Http\Controllers\Controller;
use App\Models\Finance\Tax\TaxFilingStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TaxFilingStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['tax_filing']=TaxFilingStatus::query()->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            '*.reference_number' => 'required|string',
            '*.filing_status' => 'required|integer',
            '*.tax_type' => 'required|integer',
            '*.voucher_id' => 'required_if:*.tax_type,2|integer',
            //'*.employee_id' => 'required_if:*.tax_type,1|integer',
        ]);


        try {
            DB::beginTransaction();

            $this->input = array_map(function ($data) {
                return array_merge($data, ['created_by' => auth()->id()]); // Assuming you're using Laravel authentication
            }, $this->input);
            foreach($this->input as $item){
                $taxFiling=TaxFilingStatus::query()->where('voucher_id',$item['voucher_id'])->where('tax_type',$item['tax_type'])->first();
                if($taxFiling){
                    $taxFiling->update([
                        'reference_number' => $item['reference_number'],
                        'filing_status' => $item['filing_status']
                    ]);
                }else{
                    TaxFilingStatus::create([
                        'voucher_id' => $item['voucher_id'],
                        'tax_type' => $item['tax_type'],
                        'reference_number' => $item['reference_number'],
                        'filing_status' => $item['filing_status'],
                    ]);
                }
            }



            DB::commit();
            return resp(1, 'Successful!', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }
    public function saveFile($request,$folder){

        $file = $request->file('attachment');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;

    }

    /**
     * Display the specified resource.
     */
    public function show(TaxFilingStatus $taxFilingStatus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxFilingStatus $taxFilingStatus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxFilingStatus $taxFilingStatus)
    {
        //
    }
}
