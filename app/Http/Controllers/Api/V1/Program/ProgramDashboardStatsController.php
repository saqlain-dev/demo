<?php

namespace App\Http\Controllers\Api\V1\Program;

use App\Models\Admin\FinancialYear;
use DB;
use Illuminate\Http\Request;
use App\Models\StrategicPlan;
use Illuminate\Http\Response;
use App\Models\Donar\DonarProfile;
use App\Http\Controllers\Controller;
use App\Http\Resources\rmCollection;
use App\Models\Program\ProjectDonor;
use App\Models\Progress\ProgressWorkplan;
use App\Models\Program\Rdu\ResearchMatrix;
use App\Models\Progress\IndicatorProgress;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Program\ResultResourceFramework;
use App\Models\Program\Project\MnE\ProjectMnePlan;
use Illuminate\Support\Facades\Auth;

class ProgramDashboardStatsController extends Controller
{
    public function programDashboardStats()
    {
        $this->authorizeAny([
            'dashboard-program',
        ]);

        $data['projectStatusCounts'] = $this->getProjectStatusCounts();
        $data['planning'] = $this->projects();
        $data['organizational'] = $this->strategicPlans();
        $data['progress'] = $this->progress();
        $data['monitoringAndEvalution'] = $this->monitoringAndEvalution();
        $data['researchDeliveryUnit'] = $this->researchDeliveryUnit();
        $data['projectsCount'] = $this->projectCount();
        $data['notifications'] = Auth::user()->notifications;
        $budget_expence=$this->BudgetVarianceAllProjectReport();
        $totalBudget = collect($budget_expence)->sum('total_budget');
        $totalExpenses = collect($budget_expence)->sum('total_expenses');
        $distinctProjects = collect($budget_expence)->unique('project_id')->count();

        $data['total_budget'] = $totalBudget;
        $data['total_expenses'] = $totalExpenses;
        $data['total_projects'] = $distinctProjects;
        $data['budget_expense_project_wise'] = $budget_expence;
        /*$data['las_rrf'] = ResultResourceFramework::with(['sPDetail','goalIndicators','rrf_outcomes'=>['outcomeIndicators'],'rrf_outputs'=>['outputIndicators']])->get();*/

        $data['strategic_plans'] = StrategicPlan::withCount([
            'resultResourceFrameworks' => function ($query) {
                $query->whereNull('deleted_at');
            },
            'resultResourceFrameworks as goal_indicators_count' => function ($query) {
                $query->withCount(['goalIndicators' => function ($q) {
                    $q->whereNull('deleted_at');
                }]);
            },
            'resultResourceFrameworks as rrf_outcomes_count' => function ($query) {
                $query->withCount(['rrf_outcomes' => function ($q) {
                    $q->whereNull('deleted_at');
                }]);
            },
            'resultResourceFrameworks as outcome_indicators_count' => function ($query) {
                $query->selectRaw('(SELECT COUNT(*) FROM rrf_outcomes
                            INNER JOIN outcome_indicators
                            ON rrf_outcomes.id = outcome_indicators.rrf_outcome_id
                            WHERE rrf_outcomes.result_resource_framework_id = result_resource_frameworks.id
                            AND rrf_outcomes.deleted_at IS NULL
                            AND outcome_indicators.deleted_at IS NULL) as outcome_indicators_count');
            },
            'resultResourceFrameworks as rrf_outputs_count' => function ($query) {
                $query->withCount(['rrf_outputs' => function ($q) {
                    $q->whereNull('deleted_at');
                }]);
            },
            'resultResourceFrameworks as output_indicators_count' => function ($query) {
                $query->selectRaw('(SELECT COUNT(*) FROM rrf_outputs
                            INNER JOIN output_indicators
                            ON rrf_outputs.id = output_indicators.rrf_output_id
                            WHERE rrf_outputs.result_resource_framework_id = result_resource_frameworks.id
                            AND rrf_outputs.deleted_at IS NULL
                            AND output_indicators.deleted_at IS NULL) as output_indicators_count');
            }
        ])->get();

        $work_plan_list= ProgressWorkplan::with(['workPlanOutput'=>['OutputId','OutputIndicatorId','OutputStatus'],'CreatedBy','UpdatedBy'])->orderByDesc('id')->get();
        if(!empty($work_plan_list)){
            foreach($work_plan_list as $key => $list){
                $work_plan_list[$key]['budget_expense']=$this->BudgetVarianceProjectWiseReport($list['project_id']);
            }
        }
        $data['project_output_performance']=$work_plan_list;



        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    function BudgetVarianceAllProjectReport()
    {

        // Set start and end dates
        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $startDate = $FYID->start_date;
        $endDate = $FYID->end_date;
        // $projectId = $request->input('project_id');


        $varianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                'b.id as BudgetID',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoin('chart_of_accounts as coa', 'bd.head_id', '=', 'coa.id')
            ->leftJoinSub(
                DB::table('tbl_general_ledgers as tgl')
                    ->select(
                        'tgl.project_id',
                        'tgld.NominalID as NominalID',
                        'coai.id as chart_of_account_id',
                        'tgld.NominalClassID as head_class_id',
                        DB::raw('SUM(tgld.debit) - SUM(tgld.credit) as expense_amount')
                    )
                    ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
                    ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
                    ->whereBetween('tgl.date', [$startDate, $endDate])
                    ->groupBy('tgl.project_id', 'tgld.NominalID', 'tgld.NominalClassID', 'coai.id'),
                'v',
                function ($join) {
                    $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                        ->on('hc.id', '=', 'v.head_class_id');
                }
            )
            ->whereNull('b.deleted_at')
            ->where('p.approval_status',1)
            ->groupBy('p.id', 'p.project_name', 'hc.name','b.id')
            ->get()->unique(function ($item) {
                return $item->BudgetID; // Ensure unique BudgetID & HeadClass
            })
            ->values();

        return $varianceReport;
    }
    function BudgetVarianceProjectWiseReport($projectId)
    {

        // Set start and end dates
        $FYID=FinancialYear::query()->where('status',1)->with('financialYear')->first();
        $startDate = $FYID->start_date;
        $endDate = $FYID->end_date;
        // $projectId = $request->input('project_id');


        $varianceReport = DB::table('project_profiles as p')
            ->select(
                'p.id as project_id',
                'p.project_name as project_name',
                'hc.name as head_class_name',
                'b.id as BudgetID',
                DB::raw('COALESCE(SUM(bd.program_total), 0) as total_budget'),
                DB::raw('COALESCE(SUM(v.expense_amount), 0) as total_expenses'),
                DB::raw('COALESCE(SUM(bd.program_total), 0) - COALESCE(SUM(v.expense_amount), 0) as variance')
            )
            ->leftJoin('head_classes as hc', 'hc.project_id', '=', 'p.id')
            ->leftJoin('project_budgets as b', 'p.id', '=', 'b.project_id')
            ->leftJoin('project_Budget_Details as bd', 'b.id', '=', 'bd.project_budget_id')
            ->leftJoin('chart_of_accounts as coa', 'bd.head_id', '=', 'coa.id')
            ->leftJoinSub(
                DB::table('tbl_general_ledgers as tgl')
                    ->select(
                        'tgl.project_id',
                        'tgld.NominalID as NominalID',
                        'coai.id as chart_of_account_id',
                        'tgld.NominalClassID as head_class_id',
                        DB::raw('SUM(tgld.debit) - SUM(tgld.credit) as expense_amount')
                    )
                    ->join('tbl_general_ledger_details as tgld', 'tgl.id', '=', 'tgld.Gl_Id')
                    ->join('chart_of_accounts as coai', 'tgld.NominalID', '=', 'coai.code')
                    ->whereBetween('tgl.date', [$startDate, $endDate])
                    ->groupBy('tgl.project_id', 'tgld.NominalID', 'tgld.NominalClassID', 'coai.id'),
                'v',
                function ($join) {
                    $join->on('bd.head_id', '=', 'v.chart_of_account_id')
                        ->on('hc.id', '=', 'v.head_class_id');
                }
            )
            ->whereNull('b.deleted_at')
            ->where('p.approval_status',1)
            ->where('p.id',$projectId)
            ->groupBy('p.id', 'p.project_name', 'hc.name','b.id')
            ->get()->unique(function ($item) {
                return $item->BudgetID; // Ensure unique BudgetID & HeadClass
            })
            ->values();

        return $varianceReport;
    }

    private function projectCount()
    {
        // Step 1: Fetch all projects with target_area (districts)
        $allProjects = ProjectProfile::select('*')
            ->whereNotNull('target_area')  // Ensure target_area is not null
            ->where('target_area', '!=', '')
            ->with('status')// Ensure target_area is not empty
            ->get();

        // Step 2: Group projects by province and count the projects per province
        $projectResults = DB::table('districts')
            ->select('districts.province_id', DB::raw('COUNT(DISTINCT project_profiles.id) as total_projects'))
            ->join('project_profiles', function ($join) {
                $join->on(DB::raw('CHARINDEX(CAST(districts.id AS VARCHAR), project_profiles.target_area)'), '>', DB::raw('0'));
            })
            ->groupBy('districts.province_id')
            ->get();


        // Step 3: Format project results
        $formattedProjectResults = $projectResults->filter(function ($item) {
            return !is_null($item->province_id);
        })->map(function ($item) {
            $projects = ProjectProfile::with('status') // Load related districts
            ->whereRaw("
        EXISTS (
            SELECT 1 FROM districts
            WHERE districts.province_id = ?
            AND CHARINDEX(CAST(districts.id AS VARCHAR), project_profiles.target_area) > 0
        )
    ", [$item->province_id])
                ->get();
            return [
                'area' => $this->getProvinceName($item->province_id),
                'key' => $this->getProvinceKey($item->province_id),
                'total_projects' => $item->total_projects,
                'projects' => $projects,
            ];
        });

        // Step 4: Calculate total project count across all provinces
        $totalProjects = $formattedProjectResults->sum('total_projects');

        // Add "All Provinces" to the project results
        $formattedProjectResults->prepend([
            'area' => 'All Provinces',
            'total_projects' => $totalProjects,
            'projects' => $allProjects,
        ]);

        // Step 5: Calculate unique donor count per province based on projects
        $donorCounts = $formattedProjectResults->map(function ($projectItem) {
            // Get the unique donor count for projects in this province
            $donorCount = DB::table('project_donors')
                ->join('donar_profiles', 'project_donors.donor_id', '=', 'donar_profiles.id')
                ->join('project_profiles', 'project_donors.project_id', '=', 'project_profiles.id')
                ->join('districts', function ($join) use ($projectItem) {
                    $join->on(DB::raw('CHARINDEX(CAST(districts.id AS VARCHAR), project_profiles.target_area)'), '>', DB::raw('0'))
                        ->where('districts.province_id', '=', $this->getProvinceId($projectItem['area']));
                })
                ->distinct('project_donors.donor_id')  // Ensure unique donors are counted
                ->count();

            // Add donor count to the result
            $projectItem['donor'] = $donorCount;

            return $projectItem;
        });

        return $donorCounts;
    }

    private function getProvinceName($provinceId)
    {
        return DB::table('provinces')->where('id', $provinceId)->value('name');
    }

    private function getProvinceKey($provinceId)
    {
        return DB::table('provinces')->where('id', $provinceId)->value('key');
    }

    private function getProvinceId($provinceName)
    {
        return DB::table('provinces')->where('name', $provinceName)->value('id');
    }



    private function projects(){

        // $data['totalProjectWithDonors'] = ProjectProfile::where('approval_status',1)->select('id', 'project_name')->with('donor.donorDetail')->withCount('donor')->get();

        $data['projectWithTargetArea'] = ProjectProfile::where('approval_status',1)->select('id', 'project_name', 'target_area')
        ->get()
        ->map(function ($item) {
            $targetAreas = $item->districts;

            return [
                'id' => $item->id,
                'target_area' => $targetAreas,
                'project_name' => $item->project_name,
            ];
        })
        ->toArray();

//        $data['thematicFocusSnapshot'] = ProjectProfile::where('approval_status',1)->select(
//            'thematic_area',
//            'approval_status',
//            'status',
//            DB::raw('count(id) as project_count')
//        )
//        ->with([
//            'getThematicArea:id,name',
//        ])
//        ->groupBy('thematic_area', 'approval_status', 'status')
//        ->get()
//        ->groupBy('thematic_area')
//        ->map(function ($items, $thematic_area) {
//            $thematicAreaName = $items->first()->getThematicArea ? $items->first()->getThematicArea->name : null;
//
//            $approvalStatuses = $items->mapWithKeys(function ($item) {
//                return [$item->approval_status => $item->project_count];
//            });
//
//            $projectStatuses = $items->mapWithKeys(function ($item) {
//                $key = $item->status == 1 ? 'completed' : ($item->status == 2 ? 'ongoing' : ($item->status == 3 ? 'upcoming' : $item->status));
//                return [$key => $item->project_count];
//            });
//
//            return [
//                'thematic_area_id' => $thematic_area,
//                'thematic_area_name' => $thematicAreaName,
//                'approval_statuses' => $approvalStatuses,
//                'project_statuses' => $projectStatuses,
//            ];
//        })
//        ->values()
//        ->toArray();

        $data['thematicFocusSnapshot'] = ProjectProfile::select('thematic_area', 'status', 'approval_status')
            ->with([
                'getThematicArea:id,name',
            ])
            ->where('approval_status', 1)
            ->get()
            ->groupBy('thematic_area')
            ->map(function ($items, $thematic_area) {
                $thematicAreaName = $items->first()->getThematicArea ? $items->first()->getThematicArea->name : null;

                $startedCount = $items->filter(function ($item) {
                    return $item->getAttribute('status') == 448;
                })->count();

                $offtrackCount = $items->filter(function ($item) {
                    return $item->getAttribute('status') == 449;
                })->count();

                $ontrackCount = $items->filter(function ($item) {
                    return $item->getAttribute('status') == 450;
                })->count();

                $completedCount = $items->filter(function ($item) {
                    return $item->getAttribute('status') == 451;
                })->count();

                return [
                    'thematic_area_id' => $thematic_area,
                    'thematic_area_name' => $thematicAreaName,
                    'projects_count' => $items->count(),
                    'Started' => $startedCount,
                    'Off-track' => $offtrackCount,
                    'On-track' => $ontrackCount,
                    'Completed' => $completedCount,
                ];
            })
            ->values()
            ->toArray();

        $data['donorsWithProjectCount'] = DonarProfile::select('id', 'donar_name')
        ->withCount([
            'projects',
            'projects' => function ($query) {
                $query->whereHas('projectDetail', function ($q) {
                    $q->whereNull('deleted_at'); // Exclude deleted records
                });
            },
            'projects as started' => function ($query) {
                $query->whereHas('projectDetail', function ($q) {
                    $q->where('status', 448);
                });
            },
            'projects as offtrack' => function ($query) {
                $query->whereHas('projectDetail', function ($q) {
                    $q->where('status', 449);
                });
            },
            'projects as ontrack' => function ($query) {
                $query->whereHas('projectDetail', function ($q) {
                    $q->where('status', 450);
                });
            },
            'projects as completed' => function ($query) {
                $query->whereHas('projectDetail', function ($q) {
                    $q->where('status', 451);
                });
            }
        ])
        ->get()
        ->map(function ($donor) {
            return [
                'id' => $donor->id,
                'donor_name' => $donor->donar_name,
                'projects_count' => $donor->projects_count,
                'Started' => $donor->started,
                'Off-track' => $donor->offtrack,
                'On-track' => $donor->ontrack,
                'Completed' => $donor->completed,
            ];
        });

        $data['totalProjects'] = ProjectProfile::count();
        $data['projectapprovedCount']= ProjectProfile::where('approval_status',1)->count();
        $data['projectpendingCount']= ProjectProfile::where('approval_status',2)->count();
        $data['projectrejectedCount']= ProjectProfile::where('approval_status',3)->count();
        $data['projectdraftCount']= ProjectProfile::where('approval_status',4)->count();


        $data['ProjectWithProjectGoals'] = ProjectProfile::where('approval_status',1)->select('id', 'project_name', 'created_by', 'updated_by')
        ->withCount('projectGoals')
        ->with(['projectGoals' => function($query) {
            $query->withCount('projectOutcomes')
                ->withCount('ProGoalIndicators')
                ->with(['projectOutcomes' => function($query) {
                    $query->withCount('projectOutputs')
                    ->withCount('ProOutcomeIndicators')
                        ->with(['projectOutputs' => function($query) {
                            $query->withCount('ProOutputIndicators');
                        }]);
                }]);
        }])
        ->get();
        $data['projectRrfApprovedCount']= ProjectProfile::where('approval_status',1)->where('project_rrf_approval',1)->count();
        $data['projectRrfPendingCount']= ProjectProfile::where('approval_status',1)->where('project_rrf_approval',2)->count();
        $data['projectRrfRejectedCount']= ProjectProfile::where('approval_status',1)->where('project_rrf_approval',3)->count();
        $data['projectRrfDraftCount']= ProjectProfile::where('approval_status',1)->where('project_rrf_approval',4)->count();

        // $data['rrfGoalIndicators'] = ResultResourceFramework::select('id','goal_statement')->withCount('goalIndicators')->get();
        // $data['rrfOutcomes'] = ResultResourceFramework::select('id','goal_statement')->withCount('rrf_outcomes')->get();

        $data['projectResultAndResourceSnapshot'] = IndicatorProgress::select('type_values.name as status_name', DB::raw('count(indicator_progress.id) as indicator_count'))
        ->join('type_values', 'indicator_progress.progress_status', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        return $data;
    }

    private function strategicPlans()
    {
        $data['strategicPlanList'] = StrategicPlan::select('id', 'name')
        ->withCount('indicators')
        ->withCount('pillars')
        ->get();
        $data['approvedSpList'] = StrategicPlan::query()->where('status',1)->get();
        $data['strategicPlanWithIndicatorYears'] = StrategicPlan::select('id', 'name')
        ->withCount('indicators')
        ->with('indicators.indicatorYears')
        ->get();

        $data['strategicPlanWithIndicatorOutcomes'] = StrategicPlan::select('id', 'name')
        ->withCount('indicators')
        ->with(['indicators' => function($query) {
            $query->withCount('lasGoalIndicators')
                ->withCount('lasOutcomeIndicators')
                ->withCount('lasOutputIndicators')
                ->with(['lasGoalIndicators' => function($query) {
                    $query->withCount('goalIndicatorTargets')
                        ->with('lasGoal');
                }])
                ->with(['lasOutcomeIndicators' => function($query) {
                    $query->withCount('outcomeIndicatorsTarget')
                        ->with('lasOutcome');

                }])
                ->with(['lasOutputIndicators' => function($query) {
                    $query->withCount('outputIndicatorsTarget')
                        ->with('lasOutput');
                }]);
        }])
        ->get();

        $data['rrfWithIndicatorProgress'] = ResultResourceFramework::
        withCount('goalIndicators')
        ->with(['goalIndicators' => function($query) {
            $query->with(['SpIndicatorId' => function($query) {
                $query->withCount('indicatorTargets')
                ->withCount('indicatorYears')
                ->withCount('lasGoalIndicators')
                ->withCount('lasOutputIndicators')
                ->withCount('lasOutcomeIndicators')
                ->with('indicatorTargets','indicatorYears','lasGoalIndicators','lasOutputIndicators','lasOutcomeIndicators');
            }]);

        }])
        ->get();
        $data['Total']= StrategicPlan::count();
        $data['draft']= StrategicPlan::where('status',4)->count();
        $data['pending']= StrategicPlan::where('status',2)->count();
        $data['approved']= StrategicPlan::where('status',1)->count();
        $data['rejected']= StrategicPlan::where('status',3)->count();

        $data['lasRrfDraft']= StrategicPlan::where('status',1)->where('las_rrf_approval',4)->count();
        $data['lasRrfPending']= StrategicPlan::where('status',1)->where('las_rrf_approval',2)->count();
        $data['lasRrfApproved']= StrategicPlan::where('status',1)->where('las_rrf_approval',1)->count();
        $data['lasRrfRejected']= StrategicPlan::where('status',1)->where('las_rrf_approval',3)->count();

        return $data;
    }

    private function progress()
    {
        $data['work_plan_list'] = ProgressWorkplan::select('id','project_id')->withCount(['workPlanGoals','workPlanOutcome','workPlanOutput'])
        ->with(['project' => function ($query) {
            $query->select('id', 'project_name','created_by','updated_by');
        }])->get();
        $data['draft'] = ProgressWorkplan::where('status',4)->count();
        $data['pending'] = ProgressWorkplan::where('status',2)->count();
        $data['approved'] = ProgressWorkplan::where('status',1)->count();
        $data['reject'] = ProgressWorkplan::where('status',3)->count();

        return $data;

    }

    function getProjectStatusCounts()
    {
        // Fetch combined output and indicator data
        $combinedData = DB::table('project_profiles as p')
            ->leftJoin('progress_workplan_outputs as pwo', 'p.id', '=', 'pwo.project_id')
            ->leftJoin('indicator_progress as ip', 'pwo.id', '=', 'ip.type_id')
            ->leftJoin('type_values as output_status', 'pwo.output_status', '=', 'output_status.id')
            ->leftJoin('type_values as indicator_status', 'ip.progress_status', '=', 'indicator_status.id')
            ->select(
                'p.id as project_id',
                'p.project_name',
                'p.project_code',
                'output_status.name as output_status_name',
                'indicator_status.name as indicator_status_name',
                DB::raw('COUNT(DISTINCT pwo.id) as output_count'),
                DB::raw('COUNT(DISTINCT CASE WHEN ip.type_of_indicator = 3 THEN ip.id END) as indicator_count')
            )
            ->groupBy('p.id', 'p.project_name', 'p.project_code', 'output_status.name', 'indicator_status.name')
            ->get();
        //dd($combinedData);

        // Process the data to group by project
        $groupedData = $combinedData->groupBy('project_id')->map(function ($projectGroup) {
            $firstProject = $projectGroup->first();

            $outputCounts = $projectGroup
                ->whereNotNull('output_status_name')
                ->groupBy('output_status_name')
                ->map(function ($statusGroup) {
                    return [
                        'status' => $statusGroup->first()->output_status_name,
                        'count' => $statusGroup->sum('output_count'),
                    ];
                })->values();

            $indicatorCounts = $projectGroup
                ->whereNotNull('indicator_status_name')
                ->groupBy('indicator_status_name')
                ->map(function ($statusGroup) {
                    return [
                        'status' => $statusGroup->first()->indicator_status_name,
                        'count' => $statusGroup->sum('indicator_count'),
                    ];
                })->values();

            return [
                'project_id' => $firstProject->project_id,
                'project_name' => $firstProject->project_name,
                'project_code' => $firstProject->project_code,
                'total_output' => $outputCounts->sum('count'),
                'output_count' => $outputCounts,
                'total_output_indicator' => $indicatorCounts->sum('count'),
                'indicator_count' => $indicatorCounts,
            ];
        });

        return $groupedData->values();
    }



    private function monitoringAndEvalution()
    {
        $data['mnePlansCount'] = ProjectProfile::select('id','project_name','created_by', 'updated_by')->withcount('mnePlans')->get();

        $data['totaMne'] = ProjectMnePlan::count();
        $data['draft'] = ProjectMnePlan::where('approval_status',4)->count();
        $data['pending'] = ProjectMnePlan::where('approval_status',2)->count();
        $data['approved'] = ProjectMnePlan::where('approval_status',1)->count();
        $data['reject'] = ProjectMnePlan::where('approval_status',3)->count();

        return $data;
    }

    private function researchDeliveryUnit()
    {
        $data['research_matrix_list'] =$research_matrix_list=  ResearchMatrix::select('id', 'program_name')->withCount([
            'DataSources',
            'ReserachOutputs',
            'RmResources'
        ])->with('ProgramName')->get();
        $data['draft']=$research_matrix_list->where('approval_status',4)->count();
        $data['pending']=$research_matrix_list->where('approval_status',2)->count();
        $data['approved']=$research_matrix_list->where('approval_status',1)->count();
        $data['reject']=$research_matrix_list->where('approval_status',3)->count();

        return $data;
    }

}
