<?php

namespace App\Http\Controllers\Api\V1\Finance\Estimate;

use App\Http\Controllers\Controller;
use App\Models\Donar\DonarProfile;
use App\Models\Finance\AdminInvoice\AdminInvoice;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Finance\ChartOfAccount\HeadClass;
use App\Models\Finance\ClaimTravelExpense;
use App\Models\Finance\CourtExpense;
use App\Models\Finance\CustomerHub;
use App\Models\Finance\Estimate\BudgetEstimate;
use App\Models\Finance\Estimate\BudgetEstimateDetail;
use App\Models\Finance\Grants\Nofo;
use App\Models\Finance\SubGrants\SubGrant;
use App\Models\Finance\Voucher\JournalVoucher;
use App\Models\HR\AdvanceSalary\AdvanceSalary;
use App\Models\Invoice;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\Reimbursement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BudgetEstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorizeAny([
            'manage_payment_payable',
            'manage_payment_receivable',
        ]);

        $data['jv'] = JournalVoucher::query()->with(['JournalVoucherDetail'])->get();
        //$data['invoice_list']=Invoice::query()->with('invoiceItems.itemDetail','grn.grnItem','vendorDetail','consultantContract.CcItems','workOrder.WoItems')->where('is_voucher_posted',0)->get();
        $data['invoice_list'] = Invoice::query()
            ->with('invoiceItems.itemDetail', 'grn.grnItem', 'vendorDetail', 'consultantContract.CcItems', 'workOrder.WoItems')
            ->where('is_voucher_posted', 0)
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->whereNull('deleted_at')
                    ->whereNotNull('voucher_from_id')
                    ->whereIn('voucher_from', ['PO', 'WO', 'CC', 'AR']);
            })
            ->get();
        //$data['pending-bill_list']=AdminInvoice::query()->with('CategoryId')->where(['approval_status'=>1, 'is_estimate'=> 0])->get();
        $data['pending-bill_list'] = AdminInvoice::query()
            ->with('CategoryId')
            ->where(['approval_status' => 1, 'is_estimate' => 0])
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'Admin Bill');
            })
            ->get();
        //$data['pending-reimbursement']=Reimbursement::query()->with(['EmployeeId.designation','PrId','Expenses','created_by','updated_by'])->where(['approval_status'=>1])->get();
        $data['pending-reimbursement'] = Reimbursement::query()
            ->with(['EmployeeId.designation', 'PrId', 'Expenses', 'created_by', 'updated_by'])
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'Reimbursements')
                    ->whereNotNull('voucher_from_id');
            })
            ->get();
        //$data['pending-travel-expense']=ClaimTravelExpense::query()->with(['EmployeeId.designation','PrId','created_by','updated_by'])->where(['approval_status'=>1])->get();
        $data['pending-travel-expense'] = ClaimTravelExpense::query()
            ->with(['EmployeeId.designation', 'PrId', 'created_by', 'updated_by'])
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'Travel Expense');
            })
            ->get();
        //$data['pending-court-expense']=CourtExpense::query()->with(['EmployeeId.designation','PrId','created_by','updated_by'])->where(['approval_status'=>1])->get();
        $data['pending-court-expense'] = CourtExpense::query()
            ->with(['EmployeeId.designation', 'PrId', 'created_by', 'updated_by'])
            ->where(['approval_status' => 1])
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'Court Expense');
            })
            ->get();
        //$data['grants']=Nofo::query()->with(['donor_id','FundRequestDetail'])->get();
        $data['grants'] = Nofo::query()
            ->with(['donor_id', 'FundRequestDetail'])
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'nofo');
            })
            ->get();

        $data['advanced_salary'] = AdvanceSalary::query()
            ->with(['employee','loanCategory','installments'])
            ->where('approval_status', 1)
            ->whereNotIn('id', function($query) {
                $query->select('voucher_from_id')
                    ->from('journal_vouchers')
                    ->where('voucher_from', 'LOAN');
            })
            ->get();

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:1,2,3',
            'estimate_no' => 'required',
            'date' => 'required|date',
            'address' => 'required|string',
            'total_amount' => 'required|numeric',
            'refferenceable_id' => 'required|integer',
        ]);

        switch ($this->input['type']) {
            case 1: // Payable
                $this->input['refferenceable_type'] = SubGrant::class;
                break;
            case 2: // Receiveable
                $this->input['refferenceable_type'] = Nofo::class;
                break;
            case 3: // Others
                $this->input['refferenceable_type'] = CustomerHub::class;
                break;
        }
        try {
            DB::beginTransaction();
            $budgetEstimate=BudgetEstimate::query()->create($this->input);
            if($budgetEstimate){

                if (!empty($this->input['admin_bill_id'])) {
                    // Find the AdminInvoice record with the given admin_invoice_id
                    $adminInvoice = AdminInvoice::find($this->input['admin_bill_id']);
                    // If the AdminInvoice record exists, update the 'is_estimate' column
                    if ($adminInvoice) {
                        $adminInvoice->update(['is_estimate' => 1]);
                    }
                }

                if (!empty($this->input['admin_invoice_id'])) {
                    // Find the AdminInvoice record with the given admin_invoice_id
                    $adminInvoice = Invoice::find($this->input['admin_invoice_id']);
                    // If the AdminInvoice record exists, update the 'is_estimate' column
                    if ($adminInvoice) {
                        $adminInvoice->update(['is_voucher_posted' => 1]);
                    }
                }
                foreach($request->items as $item){
                    $budgetDetail=array(
                        'budget_estimate_id'=>$budgetEstimate->id,
                        'item_detail'=>$item['item_detail'],
                        'item_coa'=>$item['item_coa'],
                        'description'=>$item['description'],
                        'quantity'=>$item['quantity'],
                        'rate'=>$item['rate'],
                        'amount'=>$item['amount'],
                        'total'=>$item['total'],
                    );
                    BudgetEstimateDetail::query()->create($budgetDetail);
                }
            }

            DB::commit();
            return resp(1, 'Successful!', $budgetEstimate->load(['budgetEstimateDetail','refferenceable']), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(BudgetEstimate $budgetEstimate)
    {
        // First, check the type column
        $type = $budgetEstimate->type; // Assuming $type is accessible from the $budgetEstimate model
        //dd($budgetEstimate);
// Define the base relationships
        $relationships = [
            'budgetEstimateDetail',
            'created_by',
            'updated_by',
            'AdminBillId',
            'AdminInvoiceId',
        ];

// Add relationships conditionally based on the value of $type
        if ($type == 1) {
            $relationships['refferenceable'] = function ($query) {
                $query->with(['NofoId.NofoDetail', 'PartnerId']);
            };
        } elseif ($type == 2) {
            $relationships['refferenceable'] = function ($query) {
                $query->with(['donor_id']);
            };
        } elseif ($type == 3) {
            $relationships['refferenceable'] = function ($query) {
                $query->with(['customerAble']);
            };
        }

// Load the relationships
        $budgetEstimate = $budgetEstimate->load($relationships);

        //dd($budgetEstimate);


        //$budgetEstimate = $budgetEstimate->load(['budgetEstimateDetail','refferenceable','created_by','updated_by']);
        return resp(1, 'Successful!', $budgetEstimate, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BudgetEstimate $budgetEstimate)
    {

        $validatedData = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:1,2,3', // 1: Payable, 2: Receivable, 3: Others
            'date' => 'required|date',
            'address' => 'required|string',
            'total_amount' => 'required|numeric',
            'refferenceable_id' => 'required|integer',
        ]);

        switch ($this->input['type']) {
            case 1: // Payable
                $this->input['refferenceable_type'] = SubGrant::class;
                break;
            case 2: // Receiveable
                $this->input['refferenceable_type'] = Nofo::class;
                break;
            case 3: // Others
                $this->input['refferenceable_type'] = CustomerHub::class;
                break;
        }

        try {
            DB::beginTransaction();
            $estimateItems=$request->items;
            unset($this->input['items']);
            $budgetEstimateUpdate = BudgetEstimate::query()->where('id', $budgetEstimate->id)->update($validatedData);
            if($budgetEstimateUpdate){
                BudgetEstimateDetail::query()->where('budget_estimate_id',$budgetEstimate->id)->delete();
                foreach($estimateItems as $item){
                    $budgetDetail=array(
                        'budget_estimate_id'=>$budgetEstimate->id,
                        'item_detail'=>$item['item_detail'],
                        'item_coa'=>$item['item_coa'],
                        'description'=>$item['description'],
                        'quantity'=>$item['quantity'],
                        'rate'=>$item['rate'],
                        'amount'=>$item['amount'],
                        'total'=>$item['total'],
                    );
                    BudgetEstimateDetail::query()->create($budgetDetail);
                }

            }

            DB::commit();
            return resp(1, 'Successful!', $budgetEstimate->load('budgetEstimateDetail'), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BudgetEstimate $budgetEstimate)
    {
        //
    }

    public function getBudgetEstimateDropdown()
    {
        $data['customer_list']=CustomerHub::query()->with('customerAble')->get();
        $data['nofo']=Nofo::all();
        $data['sub_grants']=SubGrant::all();
        $data['coa']=ChartOfAccount::all();
        $data['head_class']=HeadClass::all();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }
}
