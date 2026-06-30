<?php

namespace App\Http\Controllers\Api\V1\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Finance\BankInfo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BankInfoController extends Controller
{
   /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = BankInfo::with('lasConfiguration')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->input = $request->input();
        $request->validate([
            'bank_name' => 'required',
            'account_no' => 'required',
            'branch_no' => 'required',
            'head_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = BankInfo::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bankInfo = BankInfo::with(['lasConfiguration','HeadId'])->findOrFail($id);
        return resp('1', 'Successful!', $bankInfo, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $bankInfo = BankInfo::findOrFail($id);

        $this->input = $request->except('_method');

        $request->validate([
            'bank_name' => 'required',
            'account_no' => 'required',
            'branch_no' => 'required',
            'head_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $item = $bankInfo->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $workSheet = BankInfo::findOrFail($id);
        $workSheet->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
