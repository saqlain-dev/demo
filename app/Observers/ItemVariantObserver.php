<?php

namespace App\Observers;

use App\Models\Admin\ItemVariant; 
use App\Models\Admin\ItemVariantLog;

class ItemVariantObserver
{
    public function created(ItemVariant $variant)
    {
        $this->logChange($variant, 'created');
    }

    public function updated(ItemVariant $variant)
    {
        $changes = [
            'old' => $variant->getOriginal(),
            'new' => $variant->getChanges(),
        ];

        $this->logChange($variant, 'updated', $changes);
    }

    public function deleted(ItemVariant $variant)
    {
        $this->logChange($variant, 'deleted');
    }

    protected function logChange(ItemVariant $variant, $action, $changes = null)
    {
        ItemVariantLog::create([
            'item_variant_id' => $variant->id,
            'serial_no' => $variant->serial_no,
            'item_id' => $variant->item_id,
            'inventory_id' => $variant->inventory_id,
            'location_id' => $variant->location_id,
            'store_id' => $variant->store_id,
            'purchase_date' => $variant->purchase_date,
            'assign_to_emp' => $variant->assign_to_emp,
            'assign_to_dept' => $variant->assign_to_dept,
            'inventory_type' => $variant->inventory_type,
            'action' => $action,
            'changes' => $changes,
            'created_by' => auth()->id(),
        ]);
    }
}
