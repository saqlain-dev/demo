<?php

namespace App\Models\Admin;

use App\Models\Employee;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferNote extends Model
{

    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];


    public function TransferBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'transfer_by');
    }

    public function IssueStockId(): BelongsTo
    {
        return $this->belongsTo(IssueStock::class, 'issue_stock_id');
    }
    public function IssueStockDetailId(): BelongsTo
    {
        return $this->belongsTo(IssueStockDetail::class, 'issue_stock_detail_id');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function transferFrom(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'transfer_from');
    }
    public function transferTo(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'transfer_to');
    }

    public function receivingNote(): HasOne
    {
        return $this->hasOne(StockReceiveNote::class, 'stock_transfer_note_id');

    }
}
