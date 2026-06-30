<?php

namespace App\Http\Resources;

use App\Models\Documents;
use App\Models\TypeValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'tender_nature' => $this->tenderNature,
            'expiry_date' => $this->expiry_date,
            'closing_date' => $this->closing_date,
            'term_conditions' => $this->term_conditions,
            'required_documents' => Documents::query()->whereIn('id', explode(',', $this->documents_ids))->get(),
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'tender_details' => $this->tenderDetails,
            'approval_status' => $this->approval_status,
            'float_tender' => $this->float_tender,
            'sub_total' => $this->sub_total,
            'purchase_request_id' => $this->purchase_request_id,
            'purchase_request' => $this->purchaseRequest,
            'vendor_quotations' => $this->vendor_quotations,
            'pack_document' => $this->pack_document,
            'vendors' => $this->vendors,
            'tender_mom' => $this->tenderMinutesOfMeeting,
        ];
    }
}
