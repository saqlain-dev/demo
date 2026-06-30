<?php

namespace App\Models\Admin;

use App\Models\Employee;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IssueStock extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function IssueStockDetail(): HasMany
    {
        return $this->hasMany(IssueStockDetail::class, 'issue_stock_id');
    }

    public function StockRequestId(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class,'stock_request_id');
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
