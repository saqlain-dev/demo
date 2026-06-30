<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use DB;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\EmployeeDocument;
use App\Http\Controllers\Controller;

class EmployeeDocumentController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index()
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
            'employee_id' => 'required',
            'document_type' => 'required',
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
//            'cnic' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
//            'degree' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
//            'experience_certificate' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
//            'last_pay_slip' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
//            'reference_letter' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
//            'supporting_documents' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);
        try {
            DB::beginTransaction();

            $documentTypes = ['photo', 'cnic', 'degree', 'experience_certificate', 'last_pay_slip', 'reference_letter'];

//            foreach ($documentTypes as $docType) {
//
//                if ($request->hasFile($docType)) {
//                    $this->handleFileUpload($request, $docType);
//                }
//            }
            if($request->hasFile('file')) {
                $file = $request->file('file');
                $response = $this->saveAttachment($file, 'employee_documents');
                if ($response) {
                    $this->input['document_path'] = $response;
                    unset($this->input['file']);
                }
                EmployeeDocument::query()->create($this->input);
            }

            DB::commit();
            return resp(1, 'Successful!', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }
    private function handleFileUpload($request, $docType)
    {

        $file = $request->file($docType);
        $response = $this->saveAttachment($file, $docType);

        if ($response) {
            EmployeeDocument::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'document_type' => $docType
                ],
                [
                    'document_path' => $response
                ] + $this->input
            );
        }

    }

    public function saveAttachment($file, $folder)
    {
        $path = 'uploads/media/employee/' . $folder;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move($path, $filename);

        return $path . '/' . $filename;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $employeeDocuments = EmployeeDocument::query()->with('documentType')->where('employee_id',$id)->get();
        return resp('1', 'Successful!', $employeeDocuments, Response::HTTP_OK);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $document = EmployeeDocument::query()->findOrFail($id);
        $item = $document->delete();
        return resp(1,'Successful!', $item,Response::HTTP_CREATED);

    }
}
