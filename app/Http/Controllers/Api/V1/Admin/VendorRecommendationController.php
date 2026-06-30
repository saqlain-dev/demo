<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorRecommendation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorRecommendationController extends Controller
{
    //index
    public function index()
    {
        $vendor_recommendations = VendorRecommendation::with('tender', 'rfq', 'details.vendor', 'details.item')->get();

        return resp('1', 'Quotation updated Successfully!', $vendor_recommendations, Response::HTTP_OK);

    }

    //store
    public function store(Request $request){
        $data = $request->validate([
            'tender_id' => 'nullable|exists:tenders,id',
            'rfq_id' => 'nullable|exists:purchase_request_rfqs,id',
            'details' => 'required|array',
            'details.*.vendor_id' => 'required',
            'details.*.item_id' => 'required',
        ]);

        $tenderId = null;
        $rfqId = null;
        if(isset($data['tender_id']) && $data['tender_id']) {
            $tenderId = $data['tender_id'];
        } elseif (isset($data['rfq_id']) && $data['rfq_id']) {
            $rfqId = $data['rfq_id'];
        } else {
            return resp('0', 'Either tender_id or rfq_id must be provided.', null, Response::HTTP_BAD_REQUEST);
        }
        try {
             $vendor_recommendation = VendorRecommendation::create([
                'tender_id' => $tenderId,
                'rfq_id' => $rfqId,
                'comments' => $request->comments ?? "",
                'type' => $request->type ?? 0,
            ]);

            foreach ($data['details'] as $detail) {
                $vendor_recommendation->details()->create([
                    'vendor_id' => $detail['vendor_id'],
                    'item_id' => $detail['item_id'],
                ]);
            }

            return resp('1', 'Vendor Recommendation created Successfully!', $vendor_recommendation->load('tender', 'rfq', 'details.vendor', 'details.item'), Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            //throw $th;
            return resp('0', $th->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //show
    public function show($id){
        $vendor_recommendation = VendorRecommendation::with('tender', 'rfq', 'details.vendor', 'details.item')->find($id);
        if (!$vendor_recommendation) {
            return resp('0', 'Vendor Recommendation not found!', null, Response::HTTP_NOT_FOUND);
        }

        return resp('1', 'Vendor Recommendation retrieved successfully!', $vendor_recommendation, Response::HTTP_OK);
    }

    //update
    public function update(Request $request, $id){
        $vendor_recommendation = VendorRecommendation::find($id);
        if (!$vendor_recommendation) {
            return resp('0', 'Vendor Recommendation not found!', null, Response::HTTP_NOT_FOUND);
        }
        $data = $request->validate([
            'tender_id' => 'nullable|exists:tenders,id',
            'rfq_id' => 'nullable|exists:purchase_request_rfqs,id',
            'details' => 'required|array',
            'details.*.vendor_id' => 'required',
            'details.*.item_id' => 'required',
        ]);

        $tenderId = null;
        $rfqId = null;
        if(isset($data['tender_id']) && $data['tender_id']) {
            $tenderId = $data['tender_id'];
        } elseif (isset($data['rfq_id']) && $data['rfq_id']) {
            $rfqId = $data['rfq_id'];
        } else {
            return resp('0', 'Either tender_id or rfq_id must be provided.', null, Response::HTTP_BAD_REQUEST);
        }

        try {
            $vendor_recommendation->update([
                'tender_id' => $tenderId ?? $vendor_recommendation->tender_id,
                'rfq_id' => $rfqId ?? $vendor_recommendation->rfq_id,
                'comments' => $request->comments ?? $vendor_recommendation->comments,
                'type' => $request->type ?? $vendor_recommendation->type,
            ]);

            // Delete existing details
            $vendor_recommendation->details()->delete();

            // Add new details
            foreach ($data['details'] as $detail) {
                $vendor_recommendation->details()->create([
                    'vendor_id' => $detail['vendor_id'],
                    'item_id' => $detail['item_id'],
                ]);
            }

            return resp('1', 'Vendor Recommendation updated Successfully!', $vendor_recommendation->load('tender', 'rfq', 'details.vendor', 'details.item'), Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return resp('0', 'Something went wrong!', null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    //destroy
    public function destroy($id){
        $vendor_recommendation = VendorRecommendation::find($id);
        if (!$vendor_recommendation) {
            return resp('0', 'Vendor Recommendation not found!', null, Response::HTTP_NOT_FOUND);
        }

        try {
            $vendor_recommendation->delete();
            return resp('1', 'Vendor Recommendation deleted Successfully!', null, Response::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return resp('0', 'Something went wrong!', null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
   
}
