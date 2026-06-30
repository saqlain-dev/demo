<?php
namespace App\Http\Controllers\Api\V1\Admin;
use App\Models\InvoiceAtrDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Invoice; 
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class InvoiceAtrDetailController extends Controller
{ 


    public function store(Request $request)
    { 
        // dd(\App\Models\Admin\AirTravelRequest::first());
        $data = $request->validate([
            // Invoice fields
            'invoice_date' => 'required|date', 
            'invoice_amount' => 'required|numeric',
            'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png', 
            'atr_id' => 'required|exists:air_travel_requests,id',
            'supplier_id' => 'required|exists:vendors,id', 
            // Vehicle details
            'details' => 'required|array|min:1',
            'details.*.datetime' => 'nullable|date',
            'details.*.traveler' => 'nullable|string',
            'details.*.airline' => 'nullable|string',
            'details.*.remarks' => 'nullable|string',
            'details.*.amount' => 'nullable|numeric',
            'details.*.quotation_id' => 'nullable|exists:vendor_atr_quotations,id',
        ]);
        DB::beginTransaction();
        try { 
            $invoiceFilePath = null;
            if ($request->hasFile('invoice_file')) {
                $invoiceFilePath = $this->saveFile($request,'atr-invoice-file');
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
                'invoice_status' => 4  ,
                'atr_id'=>$request->atr_id
            ]);

            // Create associated details
            $details = [];
            foreach ($data['details'] as $item){
                if( $item['datetime']){
                    $item['datetime']=Carbon::parse($item['datetime'])->format('Y-m-d H:i:s');
                }
                $item['invoice_id'] = $invoice->id;
                $details[] = InvoiceAtrDetail::create($item);
            }

            DB::commit();
            return resp('1', 'Invoice and details created successfully!', [
                'invoice' => $invoice->load(['invoiceAtrDetail'=>['quotation']]),
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
