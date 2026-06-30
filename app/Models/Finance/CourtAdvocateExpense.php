<?php
namespace App\Models\Finance;

use App\Models\User;
use App\Models\Employee;
use App\Models\PurchaseRequest;
use App\Models\Finance\CourtExpense;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourtAdvocateExpense extends Model
{
    use HasFactory, LogEvents;
    protected $fillable=[
        'employee_id',
        'requested_date',
        'pr_id',
        'amount'
    ];

    function courtExpenses() {
        return $this->hasMany(CourtExpense::class);
    } 

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class,'pr_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
