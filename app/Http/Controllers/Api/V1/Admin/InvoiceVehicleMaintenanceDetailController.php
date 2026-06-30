<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\InvoiceVehicleMaintenanceDetail;
use Illuminate\Http\Request;
 
use App\Http\Controllers\Controller;
use App\Models\Invoice; 
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Response; 
use Illuminate\Support\Facades\Auth;   
class InvoiceVehicleMaintenanceDetailController extends Controller
{
   
    public function store(Request $request)
    { 
        // dd(\App\Models\Admin\VehicleMaintenanceForm::first()); 
        $data = $request->validate([
            // Invoice fields
            'invoice_date' => 'required|date', 
            'invoice_amount' => 'required|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',  
            'supplier_id' => 'required|exists:vendors,id', 
            'vm_id' => 'required|exists:vehicle_maintenance_forms,id',
            // Vehicle details
            'details' => 'required|array|min:1',
            'details.*.nature_of_work' => 'nullable|string',
            'details.*.qty' => 'nullable|numeric',
            'details.*.airline' => 'nullable|string',
            'details.*.remarks' => 'nullable|string',
            'details.*.estimated_unit_cost' => 'nullable|numeric',
            'details.*.amount' => 'nullable|numeric',
            'details.*.quotation_id' => 'nullable|exists:vendor_veh_maintenance_quots,id',
        ]);
        DB::beginTransaction();
        try { 
            $invoiceFilePath = null;
            if ($request->hasFile('invoice_file')) {
                $responce=$this->invoiceFile($request,'invoice_file');
                $invoiceFilePath = $responce;
            }
            $statement = DB::select("SELECT IDENT_CURRENT('invoices') as nextID");
            $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
            $inputDatetime =  $data['invoice_date'];  
            $invoice = Invoice::create([
                'invoice_number' => $inVNO,
                'invoice_date' => Carbon::createFromFormat('Y-m-d\TH:i', $inputDatetime)->format('Y-m-d H:i:s'),
                'invoice_amount' => $data['invoice_amount'],
                'supplier_id' => $data['supplier_id'],
                'invoice_file' => $invoiceFilePath, 
                'created_by' => Auth::id(),
                'invoice_status' => 4,
                'vm_id'=>$request->vm_id
            ]);
            $details = [];
            foreach ($data['details'] as $item) {  
                $item['invoice_id'] = $invoice->id;
                $details[] = InvoiceVehicleMaintenanceDetail::create($item);
            }

            DB::commit();
            return resp('1', 'Invoice and details created successfully!', [
                'invoice' => $invoice->load(['invoiceVehicleMaintenanceDetail'=>['quotation','vehicleMaintenanceForm']]),
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create invoice and details', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function invoiceFile($request,$folder){
        $folder = 'invoices';
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
