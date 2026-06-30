<?php

namespace App\Http\Controllers\Api\V1\Finance\CustomerInvoice;

use App\Http\Controllers\Controller;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\CustomerHub;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Finance\CustomerInvoice\CustomerInvoiceDetail;
use App\Models\Finance\Estimate\BudgetEstimate;
use App\Models\Finance\Estimate\BudgetEstimateDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class CustomerInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = CustomerInvoice::query()->with(['customerInvoiceDetail'])->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'total_amount' => 'required|integer',
            //'narration' => 'required',
            'date' => 'required|date',
            'address' => 'required',
            'budget_estimate_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $budgetEstimateId=$request->budget_estimate_id;
            unset($this->input['budget_estimate_id']);
            $statement = DB::select("SELECT IDENT_CURRENT('customer_invoices') as nextID");
            $inVNO='INV/'.sprintf('%04d', $statement[0]->nextID);
            $this->input['invoice_no']=$inVNO;
            $customerInvoice=CustomerInvoice::query()->create($this->input);
            if($customerInvoice){
                foreach($request->items as $item){
                    $customerInvoiceDetail=array(
                        'customer_invoice_id'=>$customerInvoice->id,
                        'budget_estimate_detail_id'=>$item['budget_estimate_detail_id'],
                        'item_detail'=>$item['item_detail'],
                        'item_coa'=>$item['item_coa'],
                        'description'=>$item['description'],
                        'quantity'=>$item['quantity'],
                        'rate'=>$item['rate'],
                        'amount'=>$item['amount'],
                        'total'=>$item['total'],
                    );
                    CustomerInvoiceDetail::query()->create($customerInvoiceDetail);
                }

            }
            BudgetEstimateDetail::query()->where('id',$budgetEstimateId)->update(array('is_invoice_posted'=>1));

            DB::commit();
            return resp(1, 'Successful!', $customerInvoice->load('customerInvoiceDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerInvoice $customerInvoice)
    {
        return resp(1, 'Successful!', $customerInvoice->load('customerInvoiceDetail'), Response::HTTP_CREATED);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerInvoice $customerInvoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerInvoice $customerInvoice)
    {
        //
    }
    public function getCustomerInvoiceDropdown()
    {
        $data['customer_list']=CustomerHub::query()->with('customerAble')->get();
        $data['coa']=ChartOfAccount::all();
        $data['head_class']=HeadClass::all();
        $budgetEstimates = BudgetEstimate::query()
            ->with(['budgetEstimateDetail', 'created_by', 'updated_by'])
            ->get();
// Loop through each BudgetEstimate and load the conditional relationships based on type
        $budgetEstimates->each(function ($budgetEstimate) {
            // Determine the type
            $type = $budgetEstimate->type;
            // Conditionally load relationships based on the type value
            if ($type == 1) {
                $budgetEstimate->load(['refferenceable' => function ($query) {
                    $query->with(['NofoId.NofoDetail', 'PartnerId']);
                }]);
            } elseif ($type == 2) {
                $budgetEstimate->load(['refferenceable' => function ($query) {
                    $query->with(['donor_id']);
                }]);
            } elseif ($type == 3) {
                $budgetEstimate->load(['refferenceable' => function ($query) {
                    $query->with(['customerAble']);
                }]);
            }
        });
        $data['estimates'] = $budgetEstimates;
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    public function getCustomerEstimate(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
        ]);

        try {
            $customerEstimate=BudgetEstimate::query()->where('customer_id',$request->customer_id)->get();
            $data['customer_estimate']=$customerEstimate->load('budgetEstimateDetail');
            $data['customer_estimate'] = $customerEstimate->load(['budgetEstimateDetail' => function ($query) {
                $query->where('is_invoice_posted', 0);
            }]);
            return resp('1', 'Invoice added Successfully!', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp('0', 'Failed to create record. Error: ' . $e->getMessage(), null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
