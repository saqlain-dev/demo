<?php

namespace App\Http\Controllers\Api\V1\Configuration;

use App\Models\EmployeeEobi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EmployeeEobiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'eobi_view',
            'finance_eobi_view',
            'manage_audit_eobi',
        ]);

        $data['listing'] = EmployeeEobi::with('created_by')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeAny([
            'eobi_create',
            'finance_eobi_create',
        ]);

        $this->input = $request->input();
        $request->validate([
            //'employee_id' => 'required',
            'date' => 'required',
            'eobi_attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'eobi_challan' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'payment_voucher' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('eobi_attachment')) {
                $file = $request->file('eobi_attachment');
                $response = $this->saveAttachment($file, 'employeeEobi');
                if ($response) {
                    $this->input['eobi_attachment'] = $response;
                }
            }

            if($request->hasFile('eobi_challan')) {
                $file = $request->file('eobi_challan');
                $response = $this->saveAttachment($file, 'employeeChallan');
                if ($response) {
                    $this->input['eobi_challan'] = $response;
                }
            }

            if($request->hasFile('payment_voucher')) {
                $file = $request->file('payment_voucher');
                $response = $this->saveAttachment($file, 'employeePaymentVouchers');
                if ($response) {
                    $this->input['payment_voucher'] = $response;
                }
            }

            $item = EmployeeEobi::query()->create($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }


    }

    public function saveAttachment($file, $folder)
    {
        $path = 'uploads/media/employee/' . $folder;

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $file->move($path, $filename);

        return $path . '/' . $filename;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->authorizeAny([
            'eobi_view',
            'finance_eobi_view',
            'manage_audit_eobi',
        ]);

        $employeeEobi = EmployeeEobi::with('created_by')->findOrFail($id);
        return resp('1', 'Successful!', $employeeEobi, Response::HTTP_OK);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAny([
            'eobi_update',
            'finance_eobi_update',
        ]);

        $employeeEobi = EmployeeEobi::findOrFail($id);

        //$this->input = $request->except('_method');

        $request->validate([
            //'date' => 'required',
            'eobi_attachment' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'eobi_challan' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
            'payment_voucher' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif',
        ]);

        try {
            DB::beginTransaction();

            if($request->hasFile('eobi_attachment')) {
                $file = $request->file('eobi_attachment');
                $response = $this->saveAttachment($file, 'employeeEobi');
                if ($response) {
                    $this->input['eobi_attachment'] = $response;
                }
            }

            if($request->hasFile('eobi_challan')) {
                $file = $request->file('eobi_challan');
                $response = $this->saveAttachment($file, 'employeeChallan');
                if ($response) {
                    $this->input['eobi_challan'] = $response;
                }
            }

            if($request->hasFile('payment_voucher')) {
                $file = $request->file('payment_voucher');
                $response = $this->saveAttachment($file, 'employeePaymentVouchers');
                if ($response) {
                    $this->input['payment_voucher'] = $response;
                }
            }

            $item = $employeeEobi->update($this->input);

            DB::commit();
            return resp(1, 'Successful!', $item, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->authorizeAny([
            'eobi_delete',
            'finance_eobi_delete',
        ]);

        $employeeEobi = EmployeeEobi::findOrFail($id);
        $employeeEobi->delete();
        return resp('1', 'Successful!', [], Response::HTTP_OK);
    }
}
