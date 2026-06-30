<?php

namespace App\Http\Controllers\Api\V1\Admin\DisposeRequest;

use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRfqDetail;
use App\Models\DisposeRequest;
use App\Models\DisposeRequestDetail;
use App\Models\Employee;
use App\Models\Program\Project\ProjectProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DisposeRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['items'] = DisposeRequestDetail::query()->with( 'disposeRequest')->orderByDesc('id')->get();
        return resp('1', 'Successfully!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dispose_request_id' => 'required|integer|exists:dispose_requests,id',
            'item_variant_id' => 'required|integer|exists:item_variants,id',
        ]);

        try {
            DB::beginTransaction();

            $prequest = DisposeRequestDetail::query()->create($this->input);

            DB::commit();
            return resp('1', 'Dispose request added Successfully!', $prequest, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(DisposeRequestDetail $disposeRequestDetail)
    {
        $data['item'] = $disposeRequestDetail->load(['disposeRequest']);
        return resp(1, 'Successful!', $data, Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DisposeRequestDetail $disposeRequestDetail)
    {

        $request->validate([
            'dispose_request_id' => 'required|integer|exists:dispose_requests,id',
            'item_variant_id' => 'required|integer|exists:item_variants,id',
        ]);

        try {
            DB::beginTransaction();

            $disposeRequestDetail->update($this->input);

            DB::commit();
            return resp('1', 'Dispose request item updated Successfully!', $disposeRequestDetail->refresh(), Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to update record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DisposeRequestDetail $disposeRequestDetail)
    {
        $disposeRequestDetail->delete();
        return resp(1, 'Dispose request item deleted successfully.', [], Response::HTTP_OK);
    }

    public function getDropdowns()
    {
        $data['projects'] = ProjectProfile::query()->select('id', 'project_name')->get();
        $data['dispose_requests'] = DisposeRequest::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getRemainingItems($id)
    {
        $dispose_request_details = DisposeRequestDetail::query()->with('itemVariant.item.itemUnit','disposeRequest')
            ->where('dispose_request_id', $id)
            ->whereDoesntHave('rfqDetails')
            ->get();

        /*$dispose_request_details = $dispose_request_details->filter(function ($item) {
            $rfq_quantity_used = PurchaseRequestRfqDetail::where('dispose_request_detail_id', $item->id)->sum('required_quantity');
            $remaining_quantity = $item->required_quantity - $rfq_quantity_used;

            $item->required_quantity = $remaining_quantity;

            // Keep items with remaining_quantity greater than 0
            return $remaining_quantity > 0;
        });
        $dispose_request_details = $dispose_request_details->values();*/

        return resp(1, 'Successful!', $dispose_request_details, Response::HTTP_CREATED);
    }

}
