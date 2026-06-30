<?php

namespace App\Http\Controllers\Api\V1\Finance\SubGrants;

use App\Http\Controllers\Controller;
use App\Models\Finance\Grants\GrantLogFramework;
use App\Models\Finance\SubGrants\SubGrant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SubGrantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'sub_grants_view',
            'manage_audit_grant_management',
        ]);

        $data = SubGrant::with(['NofoId.NofoDetail','PartnerId','created_by','updated_by','DDelegence.SgDueDelegenceDetail','SgProposal.DraftBy','SgLogFramework.DraftBy','SgContract.DraftBy','SgFundRequest.DraftBy','SgFinancialReport.DraftBy','SgCloseOut.DraftBy','SgAppriciation.DraftBy'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'sub_grants_create',
        ]);

        $request->validate([
            'partner_id' => 'required',
            'nofo_id' => 'required',
            'name' => 'required',
            'percentage' => 'required',
            'attachment' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'sub_grant');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = SubGrant::query()->create($this->input);
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
    public function show(SubGrant $subGrant): JsonResponse
    {
        $this->authorizeAny([
            'sub_grants_view',
            'manage_audit_grant_management',
        ]);

        $subGrant = $subGrant->load(['NofoId.NofoDetail','PartnerId','created_by','updated_by','DDelegence.SgDueDelegenceDetail','SgProposal.DraftBy','SgLogFramework.DraftBy','SgContract.DraftBy','SgFundRequest.DraftBy','SgFinancialReport.DraftBy','SgCloseOut.DraftBy','SgAppriciation.DraftBy']);
        return resp('1', 'Successful!', $subGrant, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubGrant $subGrant)
    {
        $this->authorizeAny([
            'sub_grants_update',
        ]);

        $request->validate([
            'partner_id' => 'required',
            'nofo_id' => 'required',
            'name' => 'required',
            'percentage' => 'required',
            //'attachment' => 'required',
        ]);

        if ($request->hasFile('attachment')) {
            $responses = $this->saveImage($request, 'sub_grant');
            $this->input['attachment'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = $subGrant->update($this->input);
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
    public function destroy(SubGrant $subGrant): JsonResponse
    {
        $this->authorizeAny([
            'sub_grants_delete',
        ]);

        $item = $subGrant->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
