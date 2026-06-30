<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use App\Models\Invoice;
use App\Models\VendorDocuments;
use App\Models\VendorQuotation;
use App\Models\WorkCompletion;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['document_list']= Documents::query()->where('status',1)->get();
        $data['vendor_document_list']= VendorDocuments::with('document')->get();
        return resp(1,'Successful!', $data,Response::HTTP_CREATED);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);


        if($request->file('file')){
            $responce=$this->uploadVendorDoc($request,'vendorDoc');
            $this->input['file']=$responce;
            $this->input['vendor_id']=auth()->user()->vendor_id;
        }

        $VendorDocuments=VendorDocuments::query()->create( $this->input);

        return resp(1,'Successful!', $VendorDocuments,Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(VendorDocuments $vendorDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorDocuments $vendorDocuments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorDocuments $vendorDocument)
    {
        $vendorDocument->delete();
        return resp(1,'Vendor document deleted successfully.', [],Response::HTTP_CREATED);
    }
    public function uploadVendorDoc($request,$folder){

        $file = $request->file('file');
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
}
