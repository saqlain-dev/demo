<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Documents;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['documents']=Documents::all();
        return resp('1', 'View Record', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'document_title' => 'required|string',
                'document_type' => 'required|integer',
                'status' => 'required|integer',
            ]);

           $document=Documents::query()->create($this->input);
            DB::commit();

            return resp('1', 'Record Created Successfully!', $document, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Documents $document)
    {
        return resp('1', 'View Record', $document, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Documents $document)
    {


        try {
            DB::beginTransaction();
            $request->validate([
                'document_title' => 'required|string',
                'document_type' => 'required|integer',
                'status' => 'required|integer',
            ]);

            Documents::query()->where('id',$document->id)->update($this->input);

            DB::commit();
            $documents=Documents::query()->findOrFail($document->id);
            return resp('1', 'Record Updated Successfully!', $document, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documents $document)
    {
        $document->delete();
        return resp('1', 'Document deleted Successfully!.', [], Response::HTTP_OK);
    }
}
