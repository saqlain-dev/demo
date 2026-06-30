<?php

namespace App\Models\Finance;

use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClaimTravelExpense extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function ExpenseDetail(): HasMany
    {
        return $this->hasMany(ClaimTravelExpenseDetail::class, 'claim_travel_expense_id');
    }

    public function PrId(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class,'pr_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
