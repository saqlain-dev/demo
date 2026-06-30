<?php
namespace App\Http\Controllers\Api\V1\Admin\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\ItemVariantLog;
use Illuminate\Http\Response;
class ItemVariantLogController extends Controller
{ 
    public function index(Request $request)
    {
        $data= ItemVariantLog::with('createdBy')
            ->when($request->item_variant_id, fn($q) => $q->where('item_variant_id', $request->item_variant_id))
            ->when($request->serial_no, fn($q) => $q->where('serial_no', 'like', "%{$request->serial_no}%"))
            ->when($request->item_id, fn($q) => $q->where('item_id', $request->item_id))
            ->when($request->inventory_id, fn($q) => $q->where('inventory_id', $request->inventory_id))
            ->when($request->location_id, fn($q) => $q->where('location_id', $request->location_id))
            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
            ->when($request->assign_to_emp, fn($q) => $q->where('assign_to_emp', $request->assign_to_emp))
            ->when($request->assign_to_dept, fn($q) => $q->where('assign_to_dept', $request->assign_to_dept))
            ->when($request->inventory_type, fn($q) => $q->where('inventory_type', $request->inventory_type))
            ->when($request->purchase_date, fn($q) => $q->whereDate('purchase_date', $request->purchase_date))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->latest()
            ->get(); 
        
        return resp(1,'Successful!', $data,Response::HTTP_OK);
    }

}
