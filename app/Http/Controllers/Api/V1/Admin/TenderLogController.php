<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\TenderLog;
use Illuminate\Http\Response;
class TenderLogController extends Controller
{
    public function index(Request $request)
    {
        $data= TenderLog::with('createdBy')
            ->when($request->tender_id, fn($q) => $q->where('tender_id', $request->tender_id))
            ->when($request->name, fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->nature_id, fn($q) => $q->where('nature_id', $request->nature_id))
            ->when($request->documents_ids, fn($q) => $q->where('documents_ids', 'like', "%{$request->documents_ids}%"))
            ->when($request->closing_date, fn($q) => $q->whereDate('closing_date', $request->closing_date))
            ->when($request->opening_date, fn($q) => $q->whereDate('opening_date', $request->opening_date))
            ->when($request->expiry_date, fn($q) => $q->whereDate('expiry_date', $request->expiry_date))
            ->when($request->purchase_request_id, fn($q) => $q->where('purchase_request_id', $request->purchase_request_id))
            ->when($request->is_comp_generated, fn($q) => $q->where('is_comp_generated', $request->is_comp_generated))
            ->when($request->approval_status, fn($q) => $q->where('approval_status', $request->approval_status))
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(20);
        return resp('1', 'Successful!', $data, Response::HTTP_OK);

    }
}
