<?php

namespace App\Http\Controllers\Api\V1\Finance\Grants;

use App\Http\Controllers\Controller;
use App\Models\Admin\Fleet\Vehicle;
use App\Models\Donar\DonarProfile;
use App\Models\Finance\Grants\GrantProposal;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GrantProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = GrantProposal::with(['NofoId.NofoDetail','DraftBy','created_by','updated_by'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nofo_id' => 'required',
            'name' => 'required',
            //'draft_by' => 'required',
            'attachment' => 'required',
            'deadline' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'grant_proposal');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = GrantProposal::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    public function saveImage($request,$folder){

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
    public function show(GrantProposal $grantProposal): JsonResponse
    {
        $grantProposal = $grantProposal->load(['NofoId.NofoDetail','DraftBy','created_by','updated_by']);
        return resp('1', 'Successful!', $grantProposal, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GrantProposal $grantProposal)
    {
        $request->validate([
            'nofo_id' => 'required',
            'name' => 'required',
            //'draft_by' => 'required',
            //'attachment' => 'required',
            'deadline' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'grant_proposal');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = $grantProposal->update($this->input);
            DB::commit();
            return resp('1', 'Record Updated Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GrantProposal $grantProposal): JsonResponse
    {
        $item = $grantProposal->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }

    public function grantDropDown()
    {
        $data['project_list']=ProjectProfile::all();
        $data['donor_list']=DonarProfile::all();
        return resp('1', ' Successfully!', $data, Response::HTTP_OK);
    }
}
