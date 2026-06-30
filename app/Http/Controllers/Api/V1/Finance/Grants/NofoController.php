<?php

namespace App\Http\Controllers\Api\V1\Finance\Grants;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Donar\DonarProfile;
use App\Models\Employee;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\Grants\Nofo;
use App\Models\Type;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class NofoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'donors_view',
            'manage_audit_grant_management',
        ]);

        $data['list'] = Nofo::with(['donor_id','created_by', 'updated_by','NofoDetail.DueDeligenceDetail.NofoDetailId','DDelegence.DueDelegenceDetail.NofoDetailId','ProposalDetail.DraftBy','ContractDetail.DraftBy','FundRequestDetail','FinancialReport.DraftBy','CloseOutDetail.DraftBy','AppreciationLetter.DraftBy','LogFramework.DraftBy'])->get();
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'donors_create',
        ]);

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'donor_id' => 'required',
            'deadline' => 'required',
            'attachments' => 'required',
        ]);

        if ($request->hasFile('attachments')) {
            $responses = $this->saveImages($request, 'nofo_images');
            $this->input['attachments'] = $responses;
        }
        try {
            DB::beginTransaction();
            $item = Nofo::query()->create($this->input);
            DB::commit();
            return resp('1', 'Record Created Successfully!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to create record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }


    public function saveImages($request, $folder)
    {
        $images = $request->file('attachments');
        $paths = [];
        // Ensure $images is an array
        if (!is_array($images)) {
            $images = [$images];
        }
        foreach ($images as $image) {
            $path = 'uploads/media/' . $folder;

            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (!file_exists('uploads/media')) {
                mkdir('uploads/media', 0777, true);
            }
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $filename = time() . '_' . $image->getClientOriginalName();
            $file_name = str_replace(' ', '_', $filename);
            $image->move($path, $file_name);

            // Save the path to the array
            $paths[] = $path . '/' . $file_name;
        }

        return json_encode($paths);
    }

    /**
     * Display the specified resource.
     */
    public function show(Nofo $nofo): JsonResponse
    {
        $this->authorizeAny([
            'donors_view',
            'manage_audit_grant_management',
        ]);

        $data['nofo'] = $nofo->load(['donor_id','created_by', 'updated_by','NofoDetail.DueDeligenceDetail.NofoDetailId','DDelegence.DueDelegenceDetail.NofoDetailId','ProposalDetail.DraftBy','ContractDetail.DraftBy','FundRequestDetail','FinancialReport.DraftBy','CloseOutDetail.DraftBy','AppreciationLetter.DraftBy','LogFramework.DraftBy']);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nofo $nofo)
    {
        $this->authorizeAny([
            'donors_update',
        ]);

        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'donor_id' => 'required',
            'deadline' => 'required',
            //'attachments' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $item = $nofo->update($this->input);
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
    public function destroy(Nofo $nofo): JsonResponse
    {
        $this->authorizeAny([
            'donors_delete',
        ]);

        $nofo->NofoDetail()->delete();
        $item = $nofo->delete();
        return resp('1', 'Record Deleted Successfully!', $item, Response::HTTP_OK);
    }
}
