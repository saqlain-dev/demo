<?php

namespace App\Http\Resources;

use App\Models\TypeValue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllowanceDeductionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'category' => $this->getCategoryDescription($this->category),
            'calculated_by' => $this->getCalculatedByDescription($this->calculated_by),
            'employee_type' => $this->whenLoaded('employeeType'),
            'value' => $this->value,
            'liter' => $this->liter,
            'employee_calculated_by' =>  $this->getCategoryDescription($this->employee_calculated_by),
            'employee_value' => $this->employee_value,
            'is_active' => $this->is_active,
            'is_taxable' => $this->is_taxable,
            'approval_status' => $this->approval_status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function getCategoryDescription($category)
    {
        // Map category to description based on your logic
        return ($category == 1) ? 'Allowance' : 'Deduction';
    }


    protected function getCalculatedByDescription($calculatedBy)
    {
        switch ($calculatedBy) {
            case 1:
                return 'Percentage';
            case 2:
                return 'Fixed Amount';
            case 3:
                return 'Per Liter';
            // Add more cases if needed
            default:
                return '';
        }
        return $calculatedBy; // Example: Return the same value for now
    }
}
