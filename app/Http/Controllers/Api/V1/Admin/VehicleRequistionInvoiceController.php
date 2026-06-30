<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\VehicleRequistionInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleRequistionInvoiceController extends Controller
{
    public function store(Request $request)
    { 
        // dd(\App\Models\Admin\AirTravelRequest::first());
        $data = $request->validate([
            // Invoice fields
            'invoice_date' => 'required|date', 
            'invoice_amount' => 'required|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png', 
            'vr_id' => 'required|exists:vehicle_requests,id',
            'supplier_id' => 'required|exists:vendors,id', 
            // Vehicle details
            'details' => 'required|array|min:1',
            'details.*.vehicle_name' => 'nullable|string',
            'details.*.quantity' => 'nullable',
            'details.*.unit_cost' => 'nullable',
            'details.*.remarks' => 'nullable|string',
            'details.*.amount' => 'nullable|numeric',
            'details.*.is_perday' => 'nullable',
            'details.*.quotation_id' => 'nullable|exists:vendor_vehicle_req_quotations,id',
        ]);
        DB::beginTransaction();
        try { 
            $invoiceFilePath = null;
            if ($request->hasFile('invoice_file')) {
                $invoiceFilePath = $this->saveFile($request,'vehicle-req-invoice-file');
            }

            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
            // Create Invoice
            $inputDatetime =  $data['invoice_date'];  
            $invoice = Invoice::create([
                'invoice_number' => $inVNO,
                'invoice_date' =>  Carbon::createFromFormat('Y-m-d\TH:i', $inputDatetime)->format('Y-m-d H:i:s'),
                'supplier_id' => $data['supplier_id'],
                'invoice_amount' => $data['invoice_amount'],
                'invoice_file' => $invoiceFilePath, 
                'created_by' => Auth::id(),
                'invoice_status' => 4,
                'vr_id' => $request->vr_id
            ]);

            // Create associated details
            $details = [];
            foreach ($data['details'] as $item){
                $item['invoice_id'] = $invoice->id;
                $item['vr_id'] = $request->vr_id;
                $details[] = VehicleRequistionInvoice::create($item);
            }

            DB::commit();
            return resp('1', 'Invoice and details created successfully!', [
                'invoice' => $invoice->load(['invoiceVRDetail'=>['quotation','vr']]),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create invoice and details', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
        
    public function saveFile($request,$folder){
        $file = $request->file('invoice_file');
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
}
