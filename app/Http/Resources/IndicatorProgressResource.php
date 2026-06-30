<?php

namespace App\Http\Resources;

use App\Models\Program\Project\ProjectRrfGoal;
use App\Models\Program\Project\ProjectRrfGoalIndicator;
use App\Models\Program\Project\ProjectRrfOutcome;
use App\Models\Program\Project\ProjectRrfOutcomeIndicator;
use App\Models\Program\Project\ProjectRrfOutput;
use App\Models\Program\Project\ProjectRrfOutputIndicator;
use App\Models\Progress\ProgressWorkplanGoals;
use App\Models\Progress\ProgressWorkplanOutcome;
use App\Models\Progress\ProgressWorkplanOutput;
use App\Models\Questionnaire\QuestionnaireForm;
use App\Models\TypeValue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndicatorProgressResource extends JsonResource
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
        $movnames = array();
        if ($this->type_of_indicator == 1) {
            $indicator_type = 'Goal';
            $indicator_parent = ProgressWorkplanGoals::query()->find($this->type_id);
            if ($indicator_parent->goal_movs_ids) {
                $movs = explode(',', $indicator_parent->goal_movs_ids);
                foreach ($movs as $key => $mov) {
                    $movnames[$key]['id'] = $mov;
                    $movnames[$key]['name'] = getAnyTablefieldName('type_values', $mov, 'name');
                }
            }
            $indicator_parent->movs = $movnames;
            $indicator = ProjectRrfGoalIndicator::query()->find($this->indicator_id);

        } elseif ($this->type_of_indicator == 2) {
            $indicator_type = 'Outcome';

            $indicator_parent = ProgressWorkplanOutcome::query()->find($this->type_id);

            if($indicator_parent->outcome_movs_ids) {
                $movs = explode(',', $indicator_parent->outcome_movs_ids);
                foreach ($movs as $key => $mov) {
                    $movnames[$key]['id'] = $mov;
                    $movnames[$key]['name'] = getAnyTablefieldName('type_values', $mov, 'name');
                }
            }
            $indicator_parent->movs = $movnames;
            $indicator = ProjectRrfOutcomeIndicator::query()->find($this->indicator_id);

        } elseif ($this->type_of_indicator == 3) {
            $indicator_type = 'Output';
            $indicator_parent = ProgressWorkplanOutput::query()->find($this->type_id);
            if ($indicator_parent->output_movs_ids) {
                $movs = explode(',', $indicator_parent->output_movs_ids);
                foreach ($movs as $key => $mov) {
                    $movnames[$key]['id'] = $mov;
                    $movnames[$key]['name'] = getAnyTablefieldName('type_values', $mov, 'name');
                }
            }
            $indicator_parent->movs = $movnames;
            $indicator = ProjectRrfOutputIndicator::query()->find($this->indicator_id);

        }
        $workplanTools = QuestionnaireForm::query()->whereIn('id', $this->form_id)->get();
        $reportingLevel = TypeValue::query()->whereIn('id', $this->reporting_level)->get();
        return [
            'id' => $this->id,
            'progress_workplan_id' => $this->ProgressWorkplanId,
            'type_of_indicator' => $indicator_type,
            'type_id' => $indicator_parent,
            'indicator_id' => $indicator,
            'progress_status' => $this->ProgressStatus,
            'kpi' => $this->kpi,
            'reporting_level' => $reportingLevel,
            'form_id' => $workplanTools,
            'created_by' => User::query()->find($this->created_by),
            'updated_by' => User::query()->find($this->updated_by),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'IndicatorMovs' => $this->IndicatorMovs??null,
            'questionnaires' => $this->questionnaires
        ];
    }
}
