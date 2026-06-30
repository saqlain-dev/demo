<?php

namespace App\Http\Controllers\Api\V1\Governance;

use App\Http\Controllers\Controller;
use App\Models\Governance\ArticleOfAssociation;
use App\Models\Governance\Memorandum;
use App\Models\Governance\MinuteOfMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MemorandumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('memorandum_view');

        $data['list']=Memorandum::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('memorandum_create');

        $request->validate([
            'title' => 'required',
            'detail' => 'required',
            // 'attachment' => 'required',
            'date' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'memorandum_docs ');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            $mom= Memorandum::query()->create($this->input);
            DB::commit();
            return resp('1', 'Memorandum added Successfully!', $mom, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to add record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Memorandum $memorandum): JsonResponse
    {
        $this->authorize('memorandum_view');

        $data['memorandum'] = $memorandum;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Memorandum $memorandum)
    {
        $this->authorize('memorandum_update');

        $request->validate([
            'title' => 'required',
            'detail' => 'required',
            //'attachment' => 'required',
            'date' => 'required',
        ]);
        if($request->file('attachment')){
            $responce=$this->saveFile($request,'memorandum_docs');
            $this->input['attachment']=$responce;
        }
        try {
            DB::beginTransaction();
            Memorandum::query()->find($memorandum->id)->update($this->input);
            $memorandum->refresh();
            DB::commit();
            return resp('1', 'Memorandum updated Successfully!', $memorandum, Response::HTTP_CREATED);
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

            mkdir('uploads/media/' . $folder, 0777, true);
        }
        $filename = time() . '_' . $file->getClientOriginalName();
        $file_name = str_replace(' ', '_', $filename);
        $file->move($path, $file_name);
        return $path.'/'.$file_name;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Memorandum $memorandum): JsonResponse
    {
        $this->authorize('memorandum_delete');

        $item = $memorandum->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
