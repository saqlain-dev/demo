<?php

namespace App\Http\Controllers\Api\V1\Finance\FinancialAnalysis;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Finance\FinancialAnalysis\ManagementReport;

class ManagementReportController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['listing'] = ManagementReport::with('preparedBy')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->input = $request->input();
        $request->validate([
            'name' => 'required',
            'worksheet_id' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $response = $this->saveAttachment($file, 'managementReport');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }

            $this->input['prepared_by'] = Auth::user()->id;

            $item = ManagementReport::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    public function saveAttachment($file, $folder){


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
    public function show($id)
    {
        $managementReport = ManagementReport::with('preparedBy')->findOrFail($id);
        return resp('1', 'Successful!', $managementReport, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $managementReport = ManagementReport::findOrFail($id);

        $this->input = $request->except('_method');

        $request->validate([
            'name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $response = $this->saveAttachment($file, 'managementReport');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }

            $item = $managementReport->update($this->input);

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
        $managementReport = ManagementReport::findOrFail($id);
        $managementReport->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
