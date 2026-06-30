<?php

namespace App\Observers;
use App\Models\Admin\Tender; 
use App\Models\Admin\TenderLog;

class TenderObserver
{
    public function created(Tender $tender)
    {
        $this->logChange($tender, 'created');
    }

    public function updated(Tender $tender)
    {
        $changes = [
            'old' => $tender->getOriginal(),
            'new' => $tender->getChanges(),
        ];

        $this->logChange($tender, 'updated', $changes);
    }

    public function deleted(Tender $tender)
    {
        $this->logChange($tender, 'deleted');
    }

    protected function logChange(Tender $tender, $action, $changes = null)
    {
        TenderLog::create([
            'tender_id' => $tender->id,
            'name' => $tender->name,
            'nature_id' => $tender->nature_id,
            'documents_ids' => $tender->documents_ids,
            'opening_date' => $tender->opening_date,
            'closing_date' => $tender->closing_date,
            'is_comp_generated' => $tender->is_comp_generated,
            'approval_status' => $tender->approval_status,
            'purchase_request_id' => $tender->purchase_request_id,
            'expiry_date' => $tender->expiry_date,
            'action' => $action,
            'changes' => $changes,
            'created_by' => auth()->id(),
        ]);
    }
}
