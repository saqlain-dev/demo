<?php
 
namespace App\Http\Controllers\Api\V1\Admin\PurchaseRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admin\PurchaseRequestRFQLog;

use Illuminate\Http\Response; 
class PurchaseRequestRFQLogController extends Controller
{
    public function index(Request $request)
    {
       $data= PurchaseRequestRFQLog::when($request->purchase_request_rfq_id, fn($q) => $q->where('purchase_request_rfq_id', $request->purchase_request_rfq_id))
            ->when($request->created_by, fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->expiry_date, fn($q) => $q->whereDate('expiry_date', $request->expiry_date))
            ->latest()
            ->paginate(20);
            
        return resp('1', 'Purchase request rfq log get Successfully!', $data, Response::HTTP_OK); 
    }

}
