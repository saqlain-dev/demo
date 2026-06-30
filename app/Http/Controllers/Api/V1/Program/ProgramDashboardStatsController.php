<?php

namespace App\Http\Controllers\Api\V1\Program;

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

        $data['planning'] = $this->projects();
        $data['organizational'] = $this->strategicPlans();
        $data['progress'] = $this->progress();
        $data['monitoringAndEvalution'] = $this->monitoringAndEvalution();
        $data['researchDeliveryUnit'] = $this->researchDeliveryUnit();
        $data['projectsCount'] = $this->projectCount();
        $data['notifications'] = Auth::user()->notifications;
        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    private function projectCount()
    {
        // Step 1: Fetch all projects with target_area (districts)
        $projects = ProjectProfile::select('id', 'target_area')
            ->whereNotNull('target_area')  // Ensure target_area is not null
            ->where('target_area', '!=', '')  // Ensure target_area is not empty
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
            return [
                'area' => $this->getProvinceName($item->province_id),
                'key' => $this->getProvinceKey($item->province_id),
                'total_projects' => $item->total_projects,
            ];
        });

        // Step 4: Calculate total project count across all provinces
        $totalProjects = $formattedProjectResults->sum('total_projects');

        // Add "All Provinces" to the project results
        $formattedProjectResults->prepend([
            'area' => 'All Provinces',
            'total_projects' => $totalProjects,
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
