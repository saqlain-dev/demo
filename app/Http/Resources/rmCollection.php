<?php

namespace App\Http\Resources;

use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class rmCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $indicator = null;
        if ($this->type == 1) {
            $indicator_type = 'Goal';
            $indicator = ProjectRrfGoalIndicator::query()->find($this->indicator_id);
        } elseif ($this->type == 2) {
            $indicator_type = 'Outcome';
            $indicator = ProjectRrfOutcomeIndicator::query()->find($this->indicator_id);

        } elseif ($this->type == 3) {
            $indicator_type = 'Output';
            $indicator = ProjectRrfOutputIndicator::query()->find($this->indicator_id);
        }
        return [
            'id' => $this->id,
            'program_name' => $this->ProgramName,
            'program_start_date' => $this->program_start_date,
            'program_end_date' => $this->program_end_date,
            'created_by' => User::query()->find($this->created_by),
            'updated_by' => User::query()->find($this->updated_by),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at,
            'progress_workplan_id' => $this->ProgressWorkplanId,
            'type' => $this->type,
            'type_id' => $this->type_id,
            'type_category_id' => $this->type_category_id,
            'research_objective' => $this->research_objective,
            'methodology_id' => $this->MethodologyId,
            'research_component_place_id' => $this->ResearchComponentPlaceId,
            'allocated_budget' => $this->allocated_budget,
            'focal_person' => $this->FocalPerson,
            'responsible' => $this->Responsible,
            'accountable' => $this->Accountable,
            'consulted' => $this->Consulted,
            'informed' => $this->Informed,
            'indicator_id' => $indicator,
            'approval_status' => $this->approval_status,
            'data_sources' => $this->dataSources ? $this->dataSources->map(function ($dataSource) {
                return [
                    'id' => $dataSource->id,
                    'data_source_id' => $dataSource->DataSourceId,
                    'data_availability' => $dataSource->DataAvailability,
                ];
            }) : [],
            'ReserachOutputs' => $this->ReserachOutputs ? $this->ReserachOutputs->map(function ($researchOutput) {
                return [
                    'id' => $researchOutput->id,
                    'research_output_start_date' => $researchOutput->research_output_start_date,
                    'research_output_end_date' => $researchOutput->research_output_end_date,
                    'research_output_id' => $researchOutput->ResearchOutputId,
                    'research_output_place_id' => $researchOutput->ResearchOutputPlaceId,
                ];
            }) : [],
            'RmResources' => $this->RmResources ? $this->RmResources->map(function ($rmResource) {
                return [
                    'id' => $rmResource->id,
                    'number_of_resources' => $rmResource->number_of_resources,
                    'allocated_program_resources' => $rmResource->AllocatedProgramResources,
                    'resources_availability' => $rmResource->ResourcesAvailability,
                ];
            }) : [],
        ];
    }
}
