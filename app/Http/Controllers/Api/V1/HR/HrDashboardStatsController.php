<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\Models\HR\Recruitment\ApplyJob;
use App\Models\HR\Recruitment\CandidateHistory;
use App\Models\HR\Recruitment\EmployeeContract;
use Carbon\Carbon;
use DB;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\HR\Complaint\Complaint;
use App\Models\HR\Leaves\EmployeeLeave;
use App\Models\HR\Recruitment\ManageJob;
use App\Models\HR\Recruitment\EmployeeRequisition;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\CollectionModify;

class HrDashboardStatsController extends Controller
{
    public function getHrDashboardStats(){

        $this->authorizeAny([
            'dashboard-hr',
            'manage_employee_portal',
        ]);

        $data['employeeCount'] = $this->employeeStatusCount();
        $data['allEmployees'] = Employee::query()->with('department','designation')->get();

        $data['department'] = Employee::select('type_values.name as department_name', DB::raw('count(employees.id) as employee_count'))
        ->join('type_values', 'employees.department_id', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['designation'] = Employee::select('designations.name as designation_name', DB::raw('count(employees.id) as employee_count'))
        ->join('designations', 'employees.designation_id', '=', 'designations.id')
        ->groupBy('designations.name')
        ->get();

        $data['employee_districts'] = Employee::select('districts.name as district_name', DB::raw('count(employees.id) as employee_count'))
        ->join('districts', 'employees.district_id', '=', 'districts.id')
        ->groupBy('districts.name')
        ->get();


        $data['attendance_report']=$this->attendance_count();
        $data['reminders']=$this->reminders();
        $data['job_interviews']=ApplyJob::query()->with('JobId')->where('status',4)->get();

        $data['gender'] = Employee::select('type_values.name as gender', DB::raw('count(employees.id) as gender_count'))
            ->join('type_values', 'employees.gender_id', '=', 'type_values.id')
            ->groupBy('type_values.name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->gender => (int)$item->gender_count
                ];
            })
            ->toArray();

        $data['complaints'] = $this->complaintsCount();
        $data['leaveRequest'] = $this->leaveRequestCount();
        $data['jobsCount'] = $this->jobsCount();
        $data['attendance'] = $this->getAttendance();
        $data['notifications'] = Auth::user()->notifications;


        return resp('1', 'Successful!', $data, Response::HTTP_OK);
    }

    private function attendance_count()
    {
        $attendance=array();
        $attendance['total_employees']=Employee::query()->count();
        $currentdata=date('Y-m-d');
        $attendanceSummary  = DB::select('EXEC AMS_DaillyAttendanceReport ?',[$currentdata]);
        $data['daily_attendance'] = json_decode(json_encode($attendanceSummary), true);
        $summary = collect($attendanceSummary)
            ->groupBy('Status') // Group records by status
            ->map(function ($group) {
                return $group->count(); // Count records in each group
            });

        $attendance['present_employee']=$summary->get('Present', 0);
        $attendance['absent_employee']=$summary->get('Absent', 0);
        $attendance['wh_employee']=$summary->get('Work from Home', 0);
        $attendance['leave'] = EmployeeLeave::where('approval_status', 1)
            ->where('start_date', '<=', $currentdata)
            ->where('end_date', '>=', $currentdata)
            ->count();

        return $attendance;
    }

    private  function reminders()
    {

        $reminders=array();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $reminders['employee_birthday'] = Employee::query()->with('department','designation')->whereMonth('date_of_birth', $currentMonth)->whereIn('employee_type',[13,15])->get();
        $reminders['work_anniversaries'] = Employee::query()->with('department','designation')->whereMonth('date_of_joining', $currentMonth)->whereIn('employee_type',[13,15])->get();
        $reminders['employee_contract_end'] = EmployeeContract::query()->with('EmployeeId','EmployeeId.department','EmployeeId.designation')->whereMonth('end_date', $currentMonth)->get();
        $probationPeriod = 3;
        $reminders['probation_employee'] = Employee::query()->with('department','designation')->whereMonth(
            DB::raw("DATEADD(MONTH, $probationPeriod, date_of_joining)"),
            $currentMonth
        )->whereIn('employee_type', [15])->get();



        //$reminders['cnic_expire'] = Employee::whereMonth('cnic_expiry', $currentMonth)->whereYear('cnic_expiry',$currentYear)->whereIn('employee_type',[13,15])->get();
        $reminders['cnic_expire'] = Employee::query()->with('department','designation')->whereYear('cnic_expiry',$currentYear)->whereIn('employee_type',[13,15])->get();

        return $reminders;
    }

    private function employeeStatusCount()
    {
        $data['totalEmployees'] = Employee::count();
        $data['approved'] = EmployeeRequisition::where('status', 1)->count();
        $data['pending'] = EmployeeRequisition::where('status', 2)->count();
        $data['rejected'] = EmployeeRequisition::where('status', 3)->count();
        $data['draft'] = EmployeeRequisition::where('status', 4)->count();

        return $data;
    }

    private function complaintsCount()
    {
        $data['totalComplaints'] = Complaint::count();
        $data['complaintDepartment'] = Complaint::select('type_values.name as department', DB::raw('count(complaints.id) as complaint_count'))
        ->join('type_values', 'complaints.department', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();
        $data['natureOfComplaint'] = Complaint::select('type_values.name as nature_of_complaint', DB::raw('count(complaints.id) as complaint_count'))
        ->join('type_values', 'complaints.nature_of_complaint', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();
        $data['underInvestigation'] = Complaint::where('complaint_status', 2)->count();
        $data['closedComplaints'] = Complaint::where('complaint_status', 1)->count();
        $data['mutualAgreement'] = Complaint::where('complaint_status', 0)->count();
        $data['draft'] = Complaint::where('complaint_status', 4)->count();
        return $data;
    }

    private function leaveRequestCount()
    {
        $currentMonth = Carbon::now()->month;
        $data['designationtWiseLeaveRequests'] = EmployeeLeave::select('designations.name as designation_name', DB::raw('count(employee_leaves.id) as leave_requests'))
        ->join('designations', 'employee_leaves.designation_id', '=', 'designations.id')->whereMonth('start_date', $currentMonth)
        ->groupBy('designations.name')
        ->get();

        // $data['DepartmentWiseLeaveRequests'] = EmployeeLeave::select('type_values.name as department', DB::raw('count(employee_leaves.id) as leave_request_count'))
        // ->join('employees', DB::raw('CAST(employee_leaves.employee_number AS NVARCHAR)'), '=', 'employees.employee_no')
        // ->join('type_values', 'employees.department_id', '=', 'type_values.id')
        // ->groupBy('type_values.name')
        // ->get();

        $data['approved'] = EmployeeLeave::where('approval_status', 1)->whereMonth('start_date', $currentMonth)->count();
        $data['pending'] = EmployeeLeave::where('approval_status', 2)->whereMonth('start_date', $currentMonth)->count();
        $data['rejected'] = EmployeeLeave::where('approval_status', 3)->whereMonth('start_date', $currentMonth)->count();
        $data['draft'] = EmployeeLeave::where('approval_status', 4)->whereMonth('start_date', $currentMonth)->count();

        return $data;
    }

    private function jobsCount()
    {
        $data['totalJobs'] = ManageJob::count();
        $data['allActiveJobs'] = ManageJob::where('status', 1)->count();
        $data['allInActiveJobs'] = ManageJob::where('status', 2)->count();

// Mapping statuses to descriptions
        $statusMapping = [
            1 => "applied",
            2 => "longListed",
            3 => "shortlisted",
            4 => "toBeInterviewed",
            5 => "interviewed",
            6 => "recommended",
            7 => "offerSent",
            8 => "hired",
            9 => "rejected",
        ];

// Get all jobs
        $jobs = ManageJob::all();

// Structure each job with apply_jobs status counts
        $data['Jobs'] = $jobs->map(function ($job) use ($statusMapping) {
            // Count statuses for apply_jobs
            $applyJobCounts = ApplyJob::query()
                ->where('job_id', $job->id)->count();

            $applyJobIds = ApplyJob::where('job_id', $job->id)->pluck('id');
            // Count statuses for candidate_histories using apply_job_ids
            $historyCounts = CandidateHistory::select('new_status as status', DB::raw('COUNT(DISTINCT apply_job_id) as count'))
                ->whereIn('apply_job_id', $applyJobIds)
                ->groupBy('new_status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Merge both counts into a single structure
            $formattedStatuses = [];
            foreach ($statusMapping as $status => $description) {
                $formattedStatuses[] = [
                    'name' => $description,
                    //'apply_job_count' => $applyJobCounts,
                    'history_count' => $historyCounts[$status] ?? 0,
                ];
            }

            return [
                'job_id' => $job->id,
                'applied_job_count' => $applyJobCounts,
                'job_name' => $job->job_title, // Assuming `job_title` column exists in ManageJob
                'statuses' => $formattedStatuses,
            ];
        })->toArray();


        $data['departmentWiseJobCount'] = ManageJob::select('type_values.name as department_name', DB::raw('count(manage_jobs.id) as job_count'))
        ->join('type_values', 'manage_jobs.department_id', '=', 'type_values.id')
        ->groupBy('type_values.name')
        ->get();

        $data['locationWiseJobCount'] = ManageJob::select('job_location', DB::raw('count(*) as job_count'))
        ->groupBy('job_location')
        ->get();

        return $data;
    }

    private function getAttendance(){
        $currentDate = date('Y-m-d');
        $rawData= \Illuminate\Support\Facades\DB::select('EXEC AMS_DaillyAttendanceReport ?', [$currentDate]);
        $data['daily_attendance'] = json_decode(json_encode($rawData), true);
        return $data;
    }
}
