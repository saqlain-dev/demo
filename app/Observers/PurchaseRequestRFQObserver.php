<?php

namespace App\Observers;

use App\Models\Admin\PurchaseRequestRFQ; 
use App\Models\Admin\PurchaseRequestRFQLog;

class PurchaseRequestRFQObserver
{
    public function created(PurchaseRequestRFQ $rfq)
    {
        $this->logChange($rfq, 'created');
    }

    public function updated(PurchaseRequestRFQ $rfq)
    {
        $changes = [
            'old' => $rfq->getOriginal(),
            'new' => $rfq->getChanges(),
        ];

        $this->logChange($rfq, 'updated', $changes);
    }

    public function deleted(PurchaseRequestRFQ $rfq)
    {
        $this->logChange($rfq, 'deleted');
    }

    protected function logChange(PurchaseRequestRFQ $rfq, $action, $changes = null)
    {
        PurchaseRequestRFQLog::create([
            'purchase_request_rfq_id' => $rfq->id,
            'expiry_date' => $rfq->expiry_date,
            'action' => $action,
            'changes' => $changes,
            'created_by' => auth()->id(), 
        ]);
    }
}
