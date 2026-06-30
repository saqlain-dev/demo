<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorQuotationDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VendorQuotationDetailController extends Controller
{
    public function awardQuotationItem(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.quotation_item_id' => 'required|exists:vendor_quotation_details,id',
            'items.*.awarded_status' => 'required|boolean',
        ]);

        $updatedItems = [];

        foreach ($request->items as $itemData) {
            $item = VendorQuotationDetail::find($itemData['quotation_item_id']);
            $item->awarded_status = $itemData['awarded_status'];
            $item->save();

            $updatedItems[] = $item;
        }
        return resp('1', 'Created Successfully!', $updatedItems, Response::HTTP_OK);

    }
}
