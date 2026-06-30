<?php

namespace App\Models\Admin;

use App\Models\Admin\Library\Book;
use App\Models\Admin\Library\BookReconciliation;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReconciliationDetail extends Model
{
    use LogEvents, SoftDeletes;
    protected $guarded = ['id'];

    public function InventoryReconciliationId(): BelongsTo
    {
        return $this->belongsTo(InventoryReconciliation::class,'inventory_reconciliation_id');
    }
    public function InventoryId(): BelongsTo
    {
        return $this->belongsTo(Inventory::class,'inventory_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
