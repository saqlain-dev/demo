<?php

namespace App\Models;

use App\Models\Admin\AirTravelRequest;
use App\Models\Admin\Fleet\VehicleRequest;
use App\Models\Finance\ClaimTravelExpenseDetail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class VisitReimbursement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    public function airTravelRequest()
    {
        return $this->belongsTo(AirTravelRequest::class, 'atr_id');
    }

    public function vehicleRequest()
    {
        return $this->belongsTo(VehicleRequest::class, 'vr_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function ExpenseDetails(): HasMany
    {
        return $this->hasMany(ClaimTravelExpenseDetail::class, 'visit_reimbursement_id');
    }

    public function reimbursementExpenses(): HasMany
    {
        return $this->hasMany(ReimbursementExpense::class, 'visit_reimbursement_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('total_amount', function (Builder $builder) {
            $builder->addSelect([
                'visit_reimbursements.*',
                DB::raw('
                    COALESCE((
                        SELECT SUM(amount) 
                        FROM claim_travel_expense_details 
                        WHERE visit_reimbursement_id = visit_reimbursements.id
                    ), 0) 
                    + 
                    COALESCE((
                        SELECT SUM(amount) 
                        FROM reimbursement_expenses 
                        WHERE visit_reimbursement_id = visit_reimbursements.id
                    ), 0) as total_amount
                ')
            ]);
        });
    }

}
