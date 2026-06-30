<?php

namespace App\Http\Resources;

use App\Models\Program\Project\MnE\ProjectMnePlan;
use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\Type;
use App\Models\TypeValue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MnePlanDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $indicator = null;
        $indicator_parent = null;
        if ($this->indicator_type == 1) {
            $indicator_type = 'Goal';
            $indicator_parent = ProjectRrfGoal::query()->find($this->indicator_parent_id);
            $indicator = ProjectRrfGoalIndicator::query()->find($this->indicator_id);

        } elseif ($this->indicator_type == 2) {
            $indicator_type = 'Outcome';
            $indicator_parent = ProjectRrfOutcome::query()->find($this->indicator_parent_id);
            $indicator = ProjectRrfOutcomeIndicator::query()->find($this->indicator_id);

        } elseif ($this->indicator_type == 3) {
            $indicator_type = 'Output';
            $indicator_parent = ProjectRrfOutput::query()->find($this->indicator_parent_id);
            $indicator = ProjectRrfOutputIndicator::query()->find($this->indicator_id);

        }

        $mneTools = QuestionnaireForm::query()->whereIn('id', $this->mne_tools)->get();
        $requiredMovs = TypeValue::query()->whereIn('id', $this->required_movs)->get();
        $disaggregates = Type::query()->whereIn('id', $this->disaggregates)->get();

        return [
            'id' => $this->id,
            'plan_id' => $this->plan,
            'indicator_type' => $this->indicator_type,
            'indicator_parent_id' => $indicator_parent,
            'indicator_id' => $indicator,
            'indicator_definition' => $this->indicator_definition,
            'data_collection_methodology' => $this->data_collection_methodology,
            'disaggregates' => $this->disaggregates,
            'disaggregates_details' => $disaggregates,
            'mne_tools' => $this->mne_tools,
            'mne_tools_details' => $mneTools,
            'data_collection_freq' => $this->dataCollectionFreq,
            'data_reporting_freq' => $this->dataReportingFreq,
            'required_movs' => $this->required_movs,
            'uploaded_movs' => $this->MnePlanDetailMovs,
            'required_movs_details' => $requiredMovs,
            'responsibility' => $this->responsibility,
            'unit_of_measure' => $this->unitOfMeasure,
            'expected_goal' => $this->expected_goal,
            'created_by' => User::query()->find($this->created_by),
            'updated_by' => User::query()->find($this->updated_by),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
