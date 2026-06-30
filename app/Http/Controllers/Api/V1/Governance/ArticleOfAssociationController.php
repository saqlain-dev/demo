<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\Governance\ArticleOfAssociation;
use App\Models\Governance\MinuteOfMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ArticleOfAssociationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'article_association_view',
            'finance_article_association_view',
        ]);

        $data['list']=ArticleOfAssociation::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'article_association_create',
            'finance_article_association_create',
        ]);

        $request->validate([
            'title' => 'required',
            'detail' => 'required',
            //'attachment' => 'required',
            'date' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'ArticleOfAssociation');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            $mom= ArticleOfAssociation::query()->create($this->input);
            DB::commit();
            return resp('1', 'Article of Association added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage() .' on line :: '.$e->getLine() , null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(ArticleOfAssociation $articleOfAssociation): JsonResponse
    {
        $this->authorizeAny([
            'article_association_view',
            'finance_article_association_view',
        ]);

        $data['articleOfAssociation'] = $articleOfAssociation;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ArticleOfAssociation $articleOfAssociation)
    {
        $this->authorizeAny([
            'article_association_update',
            'finance_article_association_update',
        ]);

        $request->validate([
            'title' => 'required',
            'detail' => 'required',
            //'attachment' => 'required',
            'date' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'ArticleOfAssociation');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            ArticleOfAssociation::query()->find($articleOfAssociation->id)->update($this->input);
            $articleOfAssociation->refresh();
            DB::commit();
            return resp('1', 'MoM updated Successfully!', $articleOfAssociation, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveFile($request,$folder)
    {
        $file = $request->file('attachment');
        $path = 'uploads/media/' . $folder;
        if (!file_exists('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (!file_exists('uploads/media')) {
            mkdir('uploads/media', 0777, true);
        }
        if (!file_exists('uploads/media/' . $folder)) {
            mkdir('uploads/media/' . $folder, 0755, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ArticleOfAssociation $articleOfAssociation): JsonResponse
    {
        $this->authorizeAny([
            'article_association_delete',
            'finance_article_association_delete',
        ]);

        $item = $articleOfAssociation->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
