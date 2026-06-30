<?php

namespace App\Models\Admin;

use App\Models\BranchOffice;
use App\Models\Employee;
use App\Models\HeadOffice;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockRequest extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function StockRequestDetail(): HasMany
    {
        return $this->hasMany(StockRequestDetail::class, 'stock_request_id');
    }

    public function DepartmentId(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department_id');
    }

    public function branchOffice(): BelongsTo
    {
        return $this->belongsTo(BranchOffice::class,'location_id');
    }
   
     public function RequestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'requested_by');
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
