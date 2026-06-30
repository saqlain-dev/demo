<?php

namespace App\Models\Finance;

use App\Models\User;
use App\Models\Employee;
use App\Traits\LogEvents;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Model;
use App\Models\Finance\CourtAdvocateExpense;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourtExpense extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    protected static function booted()
    {
        static::created(function ($courtExpense) {
            self::recalculateParentAmount($courtExpense);
        });
        static::updated(function ($courtExpense) {
            self::recalculateParentAmount($courtExpense);
        });
        static::deleted(function ($courtExpense) {
            self::recalculateParentAmount($courtExpense);
        });
    }
    public function EmployeeId(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'employee_id');
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
    protected static function recalculateParentAmount($courtExpense)
    {
        if ($courtExpense->court_advocate_expense_id){ 
            $amount = CourtExpense::where('court_advocate_expense_id',$courtExpense->court_advocate_expense_id)->sum('amount');
            if($amount){
                $courtExpense->courtAdvocateExpense->update(['amount' => $amount]);
            }
        }
    }
    public function courtAdvocateExpense()
    {
        return $this->belongsTo(CourtAdvocateExpense::class);
    }
}
