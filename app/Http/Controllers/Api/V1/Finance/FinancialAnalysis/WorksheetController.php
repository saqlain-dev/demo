<?php

namespace App\Http\Controllers\Api\V1\Finance\FinancialAnalysis;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Finance\FinancialAnalysis\WorkSheet;

class WorksheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'financial_analysis_view',
        ]);

        $data['listing'] = WorkSheet::with('resultDocument.preparedBy','managementReport.preparedBy','preparedBy')->get();
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
        $this->authorizeAny([
            'financial_analysis_create',
        ]);

        $this->input = $request->input();
        $request->validate([
            'name' => 'required',
            // 'attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'description' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $response = $this->saveAttachment($file, 'financialAnalysisWorkSheets');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }
            $this->input['prepared_by'] = Auth::user()->id;

            $item = WorkSheet::query()->create($this->input);

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
        $this->authorizeAny([
            'financial_analysis_view',
        ]);

        $workSheet = WorkSheet::with('resultDocument.preparedBy','managementReport.preparedBy','preparedBy')->findOrFail($id);
        return resp('1', 'Successful!', $workSheet, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'financial_analysis_update',
        ]);

        $workSheet = WorkSheet::findOrFail($id);

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
                $response = $this->saveAttachment($file, 'financialAnalysisWorkSheets');
                if ($response) {
                    $this->input['attachment'] = $response;
                }
            }

            $item = $workSheet->update($this->input);

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
        $this->authorizeAny([
            'financial_analysis_delete',
        ]);

        $workSheet = WorkSheet::findOrFail($id);
        $workSheet->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
