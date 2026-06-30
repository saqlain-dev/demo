<?php

namespace App\Models\Admin;

use App\Models\Admin\Library\BookReconciliationDetail;
use App\Models\Item;
use App\Models\Program\Project\ProjectProfile;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    protected $with = ['createdBy','updatedBy','item'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
    public function poDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }
    public function Reconciliation(): HasMany
    {
        return $this->hasMany(InventoryReconciliationDetail::class,'inventory_id');
    }

    public function itemVariants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'inventory_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id');
    }

}
