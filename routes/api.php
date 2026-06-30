<?php

use App\Http\Controllers\Api\V1\Admin;
use App\Http\Controllers\Api\V1\ApprovalProcess;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\Campaign;
use App\Http\Controllers\Api\V1\Communication;
use App\Http\Controllers\Api\V1\Company;
use App\Http\Controllers\Api\V1\Configuration;
use App\Http\Controllers\Api\V1\Customer;
use App\Http\Controllers\Api\V1\DesginationController;
use App\Http\Controllers\Api\V1\Donar;
use App\Http\Controllers\Api\V1\ErpActivity;
use App\Http\Controllers\Api\V1\ErpConfiguration;
use App\Http\Controllers\Api\V1\Finance;
use App\Http\Controllers\Api\V1\Governance;
use App\Http\Controllers\Api\V1\HR;
use App\Http\Controllers\Api\V1\Inquiry;
use App\Http\Controllers\Api\V1\Lead;
use App\Http\Controllers\Api\V1\Opportunity;
use App\Http\Controllers\Api\V1\PDU;
use App\Http\Controllers\Api\V1\Program;
use App\Http\Controllers\Api\V1\Progress;
use App\Http\Controllers\Api\V1\Prospect;
use App\Http\Controllers\Api\V1\Questionnaire;
use App\Http\Controllers\Api\V1\Quotation;
use App\Http\Controllers\Api\V1\StrategicPlan;
use App\Http\Controllers\Api\V1\Supplier;
use App\Http\Controllers\Api\V1\Task;
use App\Http\Controllers\Api\V1\TypeValue;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\V1\RFP;
use App\Http\Controllers\Api\V1\RFQ;
use App\Http\Controllers\Api\V1\ErpPurchaseOrder;
use App\Http\Controllers\Api\V1\SalesOrder;
use App\Http\Controllers\Api\V1\Dashboard;
use App\Http\Controllers\Api\V1\Division;
use App\Http\Controllers\Api\V1\SalesTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('auth/login', [Auth\LoginController::class,'login']);
Route::get('view-jobs', [App\Http\Controllers\Api\V1\HR\Recruitment\ManageJobController::class,'viewJobs']);
Route::get('get-job-detail/{item}', [App\Http\Controllers\Api\V1\HR\Recruitment\ManageJobController::class,'getJobDetail']);
Route::post('apply-on_job', [App\Http\Controllers\Api\V1\HR\Recruitment\ManageJobController::class,'applyOnJob']);
Route::post('vendor/login', [Auth\VendorLoginController::class,'login']);
Route::post('vendor/signup', [Auth\VendorLoginController::class,'vendorSignup']);

Route::get('public-inquiry-dropdown',[Inquiry\InquiryController::class, 'getInquiryDropdown']);
Route::apiResource('public-inquiry',Inquiry\InquiryController::class)->only(['store']);

// Candidate Online Test
Route::get('get-online-test/{uuid}', [HR\Recruitment\CandidateOnlineTestController::class,'getOnlineTest']);
Route::post('start-online-test-counter', [HR\Recruitment\CandidateOnlineTestController::class,'updateTestStartedAt']);
Route::post('submit-online-test', [HR\Recruitment\CandidateOnlineTestController::class,'submitOnlineTest']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return resp(1,'Successful!', $request->user());
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('abilities', [Auth\LoginController::class, 'abilities']);
    Route::post('auth/logout', [Auth\LoginController::class,'logout']);
    Route::get('profile', [Auth\LoginController::class, 'show']);

    Route::post('send-email', [EmailController::class, 'sendEmail']);
    Route::post('send-notification', [NotificationController::class, 'sendNotification']);
    Route::get('notifications', [NotificationController::class, 'getNotifications']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Roles and Permissions
    Route::post('roles/revoke-permission',[Admin\RoleController::class, 'revokePermission']);
    Route::post('roles/attach-permission',[Admin\RoleController::class, 'attachPermission']);
    Route::post('roles/sync-permissions',[Admin\RoleController::class, 'syncPermission']);
    Route::post('roles/sync-user-roles',[Admin\RoleController::class, 'syncUserRoles']);
    Route::post('roles/sync-multiple-users-roles',[Admin\RoleController::class, 'syncMultipleUserRoles']);
    Route::apiResource('roles',Admin\RoleController::class);

    Route::get('permission-dropdowns',[Admin\PermissionController::class, 'getDropdowns']);
    Route::get('perm-list',[Admin\PermissionController::class, 'permList']);
    Route::get('get-roles-and-perms',[Admin\PermissionController::class, 'getRolePermList']);
    Route::get('get-users-with-permissions',[Admin\PermissionController::class, 'getUsersWithPermissions']);
    Route::get('get-users-with-roles',[Admin\PermissionController::class, 'getUsersWithRoles']);
    Route::apiResource('permissions',Admin\PermissionController::class);

    // Strategic plan
    Route::get('strategic-plan/dd-list', [StrategicPlan\StrategicPlanController::class, 'getDdList'])->name('strategic-plan.dd-list');
    Route::post('strategic-plans/update-status', [StrategicPlan\StrategicPlanController::class, 'updateStatus']);
    Route::apiResource('strategic-plans',StrategicPlan\StrategicPlanController::class);
    Route::post('strategic-plan/delete-years/{item}', [StrategicPlan\StrategicPlanController::class, 'deleteYears'])->name('strategic-plans.delete-years');
    Route::post('strategic-plans/save-pillars', [StrategicPlan\StrategicPlanController::class, 'savePillars']);
    Route::get('strategic-plans/get-pillars/{item}', [StrategicPlan\StrategicPlanController::class, 'getPillars']);
    Route::post('strategic-plans/save-indicator', [StrategicPlan\StrategicPlanController::class, 'saveIndicators']);
    Route::post('strategic-plans/indicator-actual-value/{item}', [StrategicPlan\StrategicPlanController::class, 'saveIndicatorYearActualValue']);
    Route::post('sp-update/{id}',[StrategicPlan\StrategicPlanController::class,'updateSP']);
    Route::post('sp-delete-pillar/{id}',[StrategicPlan\StrategicPlanController::class,'deletePillar']);
    Route::post('sp-update-pillar/{item}',[StrategicPlan\StrategicPlanController::class,'updatePillars']);
    Route::post('sp-delete-indicator/{item}',[StrategicPlan\StrategicPlanController::class,'deleteIndicator']);
    Route::post('sp-update-indicator/{item}',[StrategicPlan\StrategicPlanController::class,'updateIndicators']);
    Route::post('sp-approval/{item}',[StrategicPlan\StrategicPlanController::class,'sendForApproval']);
    Route::post('sp-update-req/{item}',[StrategicPlan\StrategicPlanController::class,'updateRequest']);
    Route::post('request-approval-status',[StrategicPlan\StrategicPlanController::class,'checkRequestStatus']);

    // Project profile
    Route::get('project-profiles/get-dropdowns', [Program\Project\ProjectProfileController::class, 'getDropDowns']);
    Route::get('project-profiles/get-approve-projects', [Program\Project\ProjectProfileController::class, 'getApprovedProjects']);
    Route::get('project-profiles/update-status', [Program\Project\ProjectProfileController::class, 'updateStatus']);
    Route::post('project-profiles-approval/{item}', [Program\Project\ProjectProfileController::class, 'sendProjectForApproval']);
    Route::post('project-rrf-approval/{item}', [Program\Project\ProjectProfileController::class, 'sendProjectRRFForApproval']);
    Route::get('program-dashboard-stats', [Program\ProgramDashboardStatsController::class, 'programDashboardStats']);

    Route::apiResource('project-profiles', Program\Project\ProjectProfileController::class);
    Route::get('project-profile-rrf-report', [Program\Project\ProjectProfileController::class,'getProjectProfilesRRFReport']);

    // Types
    Route::get('types/get-disaggregates', [TypeValue\TypeController::class, 'getDisaggregates']);
    Route::apiResource('types', TypeValue\TypeController::class);
    Route::patch('types/{id}/restore', [TypeController::class, 'restore']);
    Route::get('types/get-values/{type}', [TypeValue\TypeController::class, 'getTypeValues']);

    // Type Values
    Route::apiResource('type-values', TypeValue\TypeValueController::class);

    Route::post('type-values/{id}/restore', [TypeValue\TypeValueController::class, 'restore']);

    // Implementing partners
    Route::post('project-implementing-partners-upload-logo',[Program\ProjectImplementingPartnerController::class,'uploadLogo']);
    Route::apiResource('project-implementing-partners', Program\ProjectImplementingPartnerController::class);

    // Head offices
    Route::apiResource('head-offices', Configuration\HeadOfficeController::class);
    // Branch offices
    Route::apiResource('branch-offices',Configuration\BranchOfficeController::class);
    // Employee
    Route::post('employee-leave-balance',[Configuration\EmployeeController::class,'getLeaveBalanceByID']);
    Route::post('employee-status-change',[Configuration\EmployeeController::class,'employeeStatusChange']);
    Route::post('employee-status-change-approval/{item}',[Configuration\EmployeeController::class,'sendEmpStatusChangeReqForApproval']);
    Route::get('employee-status-change-listing', [Configuration\EmployeeController::class, 'employeeStatusChangeRequestListing']);
    Route::get('approved-status-change-listing', [Configuration\EmployeeController::class, 'employeeApprovedStatusChangeRequestListing']);
    Route::get('employee/add-employee', [Configuration\EmployeeController::class, 'addEmployee']);

    // Employee Qualification
    Route::get('employee/qualification/{id}', [Configuration\EmployeeController::class, 'qualification']);
    Route::post('employee/save-qualification', [Configuration\EmployeeController::class, 'saveQualification']);
    Route::post('employee/update-qualification', [Configuration\EmployeeController::class, 'updateQualification']);
    Route::delete('employee/delete-qualification/{item}', [Configuration\EmployeeController::class, 'deleteQualification']);
    // Employee Experience
    Route::get('employee/experience/{id}', [Configuration\EmployeeController::class, 'experience']);
    Route::post('employee/save-experience', [Configuration\EmployeeController::class, 'saveExperience']);
    Route::post('employee/update-experience/{item}', [Configuration\EmployeeController::class, 'updateExperience']);
    Route::delete('employee/delete-experience/{item}', [Configuration\EmployeeController::class, 'deleteExperience']);
    // Employee Picture
    Route::post('employee/update-profile/{item}', [Configuration\EmployeeController::class, 'updatePicture']);
    Route::post('employee/update-employee-leave', [Configuration\EmployeeController::class, 'updateEmployeeLeave']);

    Route::apiResource('employee',Configuration\EmployeeController::class);
    // LAS RRF GOALs
    Route::apiResource('result-resource-framework', Program\ResultResourceFrameworkController::class);
    Route::post('las-rrf-goals/save-indicator', [Program\ResultResourceFrameworkController::class, 'addGoalIndicators']);
    Route::post('las-rrf-goals/update-indicator', [Program\ResultResourceFrameworkController::class, 'updateGoalIndicators']);
    Route::delete('las-rrf-goals/delete-indicator/{item}', [Program\ResultResourceFrameworkController::class, 'deleteGoalIndicators']);

    // RRF output
    //Route::apiResource('result-resource-framework-output', Program\ResultResourceFrameworkOutputController::class);
    Route::apiResource(
        'result-resource-framework-output',
        Program\ResultResourceFrameworkOutputController::class,
        ['parameters' => [
            'result-resource-framework-output' => 'output'
        ]]
    );

    Route::post('las-rrf-output/save-indicator', [Program\ResultResourceFrameworkOutputController::class, 'addOutputIndicators']);
    Route::post('las-rrf-output/update-indicator', [Program\ResultResourceFrameworkOutputController::class, 'updateOutputIndicators']);
    Route::delete('las-rrf-output/delete-indicator/{item}', [Program\ResultResourceFrameworkOutputController::class, 'deleteOutputIndicators']);

    // RRF Outcome
    //Route::apiResource('result-resource-framework-outcome', Program\ResultResourceFrameworkOutcomeController::class);
    Route::apiResource(
        'result-resource-framework-outcome',
        Program\ResultResourceFrameworkOutcomeController::class,
        ['parameters' => [
            'result-resource-framework-outcome' => 'outcome'
        ]]
    );

    Route::post('las-rrf-outcome/save-indicator', [Program\ResultResourceFrameworkOutcomeController::class, 'addOutcomeIndicators']);
    Route::post('las-rrf-outcome/update-indicator', [Program\ResultResourceFrameworkOutcomeController::class, 'updateOutcomeIndicators']);
    Route::delete('las-rrf-outcome/delete-indicator/{item}', [Program\ResultResourceFrameworkOutcomeController::class, 'deleteOutcomeIndicators']);

    Route::get('get-rrf-by-sp-id/{item}', [Program\ResultResourceFrameworkController::class, 'getRrfBySpId']);
    Route::post('las-rrf-approval/{item}', [Program\ResultResourceFrameworkController::class, 'sendLasRRFForApproval']);
    Route::get('get-sp-oc-op-sp-id/{item}', [Program\ResultResourceFrameworkController::class, 'getSpOutcomeOutputbySpId']);
    Route::get('get-outcome-by-goal-id/{item}', [Program\ResultResourceFrameworkOutcomeController::class, 'getOutcomeByGoalId']);
    Route::get('get-output-by-outcome-id/{item}', [Program\ResultResourceFrameworkOutputController::class, 'getOutputByOutcomeId']);
    Route::get('get-indicator-sp-id/{item}', [StrategicPlan\StrategicPlanController::class, 'getSpIndicatorBySpId']);

    Route::apiResource(
        'project-rrf-goal',
        Program\Project\ProjectRrfGoalController::class,
        ['parameters' => [
            'project-rrf-goal' => 'goal'
        ]]
    );

    Route::post('project-rrf-goals/save-indicator', [Program\Project\ProjectRrfGoalController::class, 'addProjGoalIndicators']);
    Route::post('project-rrf-goals/update-indicator', [Program\Project\ProjectRrfGoalController::class, 'updateProjGoalIndicators']);
    Route::delete('project-rrf-goals/delete-indicator/{item}', [Program\Project\ProjectRrfGoalController::class, 'deleteProjGoalIndicators']);

    Route::apiResource(
        'project-rrf-outcome',
        Program\Project\ProjectRrfOutcomeController::class,
        ['parameters' => [
            'project-rrf-outcome' => 'outcome'
        ]]
    );

    Route::post('project-rrf-outcome/save-indicator', [Program\Project\ProjectRrfOutcomeController::class, 'addProjOutcomeIndicators']);
    Route::post('project-rrf-outcome/update-indicator', [Program\Project\ProjectRrfOutcomeController::class, 'updateProjOutcomeIndicators']);
    Route::delete('project-rrf-outcome/delete-indicator/{item}', [Program\Project\ProjectRrfOutcomeController::class, 'deleteProjOutcomeIndicators']);
    Route::get('project-outcome-by-goal/{item}', [Program\Project\ProjectRrfOutcomeController::class, 'getOutcomeByGoalId']);
    Route::apiResource(
        'project-rrf-output',
        Program\Project\ProjectRrfOutputController::class,
        ['parameters' => [
            'project-rrf-output' => 'output'
        ]]
    );

    Route::post('project-rrf-output/save-indicator', [Program\Project\ProjectRrfOutputController::class, 'addProjOutputIndicators']);
    Route::post('project-rrf-output/update-indicator', [Program\Project\ProjectRrfOutputController::class, 'updateProjOutputIndicators']);
    Route::delete('project-rrf-output/delete-indicator/{item}', [Program\Project\ProjectRrfOutputController::class, 'deleteProjOutputIndicators']);

    Route::get('get-output-by-outcome/{item}', [Program\Project\ProjectRrfOutputController::class, 'getOutputByOutcomeId']);
    Route::get('get-project-oc-op-by-pid/{item}', [Program\Project\ProjectRrfOutputController::class, 'getProjectOutcomeOutputByProjectId']);

    Route::apiResource(
        'project-kpi-mapping',
        Program\Project\ProjectKpiMappingController::class,
        ['parameters' => [
            'project-kpi-mapping' => 'projectKpiMapping'
        ]]
    );

    // Progress Work plan
    Route::post('project-workplan/{item}', [Progress\ProgressWorkplanController::class, 'sendProjectWorkplanForApproval']);
    Route::apiResource('progress-workplan', Progress\ProgressWorkplanController::class);
    Route::get('get-project-workplan-by-id/{item}',[Progress\ProgressWorkplanController::class,'getWorkplanbyWkplid']);
    Route::get('get-workplan-goal/{wkpid}', [Progress\ProgressWorkplanController::class, 'getWorkPlanGoalByWorkplanId']);

    Route::get('get-workplan-goal-by-id/{ProgressWorkplanGoals}', [Progress\ProgressWorkplanController::class, 'getWorkPlanGoalById']);
    Route::post('add-workplan-goal', [Progress\ProgressWorkplanController::class, 'addWorkPlanGoal']);
    Route::post('update-workplan-goal/{ProgressWorkplanGoals}', [Progress\ProgressWorkplanController::class, 'updateWorkPlanGoal']);
    Route::delete('delete-workplan-goal/{item}', [Progress\ProgressWorkplanController::class, 'deleteWorkplanGoal']);


    Route::get('get-workplan-outcomes-by-goal/{wkpid}/{goalid}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutcomesByGoal']);

    Route::get('get-workplan-outcomes-by-wkpid/{wkpid}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutcomesbyWkpid']);

    Route::get('get-workplan-outcome-by-id/{ProgressWorkplanOutcome}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutcomeByid']);
    Route::post('add-workplan-outcomes', [Progress\ProgressWorkplanController::class, 'addWorkPlanOutcomes']);
    Route::post('update-workplan-outcome/{ProgressWorkplanOutcome}', [Progress\ProgressWorkplanController::class, 'updateWorkPlanOutcome']);
    Route::delete('delete-workplan-outcome/{item}', [Progress\ProgressWorkplanController::class, 'deleteWorkplanOutcome']);

    Route::get('get-workplan-output-by-outcome/{wkpid}/{outcomeid}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutputByOutcome']);
    Route::get('get-workplan-output-by-wkpid/{wkpid}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutputByWkpid']);

    Route::get('get-workplan-output-by-id/{ProgressWorkplanOutput}', [Progress\ProgressWorkplanController::class, 'getWorkPlanOutputByid']);
    Route::post('add-workplan-output', [Progress\ProgressWorkplanController::class, 'addWorkPlanOutputs']);
    Route::put('update-workplan-output/{ProgressWorkplanOutput}', [Progress\ProgressWorkplanController::class, 'updateWorkPlanOutput']);
    Route::delete('delete-workplan-output/{item}', [Progress\ProgressWorkplanController::class, 'deleteWorkplanOutput']);


    Route::apiResource('workplan-activites', Progress\WorkplanActivitiesController::class);
    Route::get('get-activities-by-type/{item}', [Progress\WorkplanActivitiesController::class, 'getActivitiesBytypeId']);
    Route::get('get-workplan-dropdown', [Progress\ProgressWorkplanController::class, 'getDropDowns']);
    Route::post('get-movs-name-by-type', [Progress\ProgressWorkplanController::class, 'getMovsNameByType']);

    Route::apiResource('indicator-progress', Progress\IndicatorProgressController::class);
    Route::post('indicator-progress/save-movs' ,[Progress\IndicatorProgressController::class, 'saveIndicatorMovs']);
    Route::post('indicator-progress/{item}/form/{form}/fill-form' ,[Progress\IndicatorProgressController::class, 'fillForm']);
    Route::get('indicator-progress/{item}/form/{form}/responses' ,[Progress\IndicatorProgressController::class, 'getFormResponses']);
    Route::get('get-indicator-progress-dropdown', [Progress\IndicatorProgressController::class, 'getDropDowns']);
    Route::get('get-indicator-progress-wkpid/{item}', [Progress\IndicatorProgressController::class, 'getIndicatorProgressWkpid']);


    Route::get('get-outcome-by-goal-id/{item}', [Program\ResultResourceFrameworkOutcomeController::class, 'getOutcomeByGoalId']);
    Route::get('get-output-by-outcome-id/{item}', [Program\ResultResourceFrameworkOutputController::class, 'getOutputByOutcomeId']);

    //RDU
    Route::post('research-matrix-approval/{item}',[Program\Rdu\ResearchMatrixController::class,'sendResearchMatrixForApproval']);
    Route::apiResource('research-matrix', Program\Rdu\ResearchMatrixController::class);
    Route::apiResource('rm-data-source', Program\Rdu\ResearchMatrixDataSourcesController::class);
    Route::get('get-rm-datasource-by-rmid/{item}', [Program\Rdu\ResearchMatrixDataSourcesController::class,'getRMDataSourceByRmid']);
    Route::apiResource('rm-research-output', Program\Rdu\ResearchMatrixResearchOutputController::class);
    Route::get('get-research-output-rmid/{item}', [Program\Rdu\ResearchMatrixResearchOutputController::class,'getRMResearchOutByRmid']);
    Route::apiResource('rm-resources', Program\Rdu\ResearchMatrixResourcesController::class);
    Route::get('get-rm-resources-rmid/{item}', [Program\Rdu\ResearchMatrixResourcesController::class,'getRMResourcesByRmid']);

    Route::get('research-matrix-dropdown', [Program\Rdu\ResearchMatrixController::class,'getRMDropDown']);
    // Admin
    Route::get('vendor-dropdown', [Admin\VendorController::class, 'getVendorDropDown']);
    Route::get('admin-dashboard', [Admin\AdminDashboardStatsController::class, 'adminDashboardStats']);
    Route::get('get-vendor-rfqs', [Admin\VendorController::class, 'vendorRfqList']);
    Route::get('get-rfq-detail/{id}', [Admin\VendorController::class, 'rfqDetailByID']);
    Route::get('get-tender-list', [Admin\VendorController::class, 'tenderList']);
    Route::get('expire-tender-list', [Admin\VendorController::class, 'expireTenderList']);
    Route::get('expire-RFQ-list', [Admin\VendorController::class, 'expireRfqList']);
    Route::get('applied-tender-list', [Admin\VendorController::class, 'appliedTenderList']);
    Route::get('applied-RFQ-list', [Admin\VendorController::class, 'appliedRfqList']);
    Route::get('view-tender/{tender}', [Admin\VendorController::class, 'viewTender']);
    Route::post('save-vendor-contact/{vendor}', [Admin\VendorController::class, 'saveContactPerson']);
    Route::post('save-vendor-address/{vendor}', [Admin\VendorController::class, 'saveVendorAddress']);
    Route::post('upload-profile-pic/{vendor}', [Admin\VendorController::class, 'updateVProfilePicture']);
    Route::post('update-vendor-status', [Admin\VendorController::class, 'updateVendorStatus']);
    Route::post('applied-atr', [Admin\VendorController::class, 'appliedAtr']);
    Route::post('applied-vr', [Admin\VendorController::class, 'appliedVR']);
    Route::post('applied-vm', [Admin\VendorController::class, 'appliedVM']);
    Route::post('send-ticket-invoice', [Admin\VendorController::class, 'sendAirTicketInvoice']);
    Route::post('send-vr-ticket-invoice', [Admin\VendorController::class, 'sendVRTicketInvoice']);
    Route::post('send-vm-invoice', [Admin\VendorController::class, 'sendVMInvoice']);
    Route::get('vr-listing', [Admin\VendorController::class,'vendorVRList']);
    Route::get('vm-listing', [Admin\VendorController::class,'vendorVMList']);
    Route::apiResource('vendor', Admin\VendorController::class);

    //Fleet
    Route::apiResource('vehicle', Admin\Fleet\VehicleController::class);
    Route::post('save-vehicle-request-vendor/{vr}', [Admin\Fleet\VehicleRequestController::class,'attachVRVendor']);
    Route::post('vehicle-request-approval/{item}',[Admin\Fleet\VehicleRequestController::class,'sendVehicleRequestForApproval']);
    Route::apiResource('vehicle-request', Admin\Fleet\VehicleRequestController::class);
    Route::apiResource('vehicle-request-detail', Admin\Fleet\VehicleRequestDetailController::class);
    //Route::apiResource('chauffeur', Admin\Fleet\ChauffeurController::class);
    Route::apiResource('incident-report', Admin\Fleet\IncidentReportController::class);
    Route::apiResource('corrective-action', Admin\Fleet\CorrectiveActionController::class);

    // Vehicle Travel Request Quotation
    Route::post('accept-vr-quotation/{vr}', [Admin\Fleet\VehicleRequestController::class,'acceptVRQuotation']);
    Route::get('get-vr-quotation/{vr}', [Admin\VendorVehicleReqQuotationController::class,'getVRQuotationList']);
    Route::apiResource('vr-quotation', Admin\VendorVehicleReqQuotationController::class);

    Route::apiResource('assign-vehicle', Admin\Fleet\AssignVehicleController::class);

    Route::apiResource('feed-back-question', Admin\Fleet\FeedBackQuestionController::class);

    Route::apiResource('feed-back', Admin\Fleet\FeedBackController::class);


    Route::apiResource('feed-back-details', Admin\Fleet\FeedBackParentController::class);
    // Fuel
    Route::post('fuel-request-approval/{item}',[Admin\Fleet\FuelRequestController::class,'sendFuelRequestForApproval']);
    Route::apiResource('fuel-request', Admin\Fleet\FuelRequestController::class);
    Route::apiResource('fuel-consumption', Admin\Fleet\FuelConsumptionController::class);
    Route::apiResource('log-book', Admin\Fleet\LogBookController::class);
    Route::apiResource('route-management', Admin\Fleet\RouteManagementController::class);
    Route::apiResource('route-commuter', Admin\Fleet\RouteCommuterController::class);
    Route::get('fleet-dropdown', [Admin\Fleet\IncidentReportController::class,'getFleetDropDown']);

    //Library
    Route::apiResource('books', Admin\Library\BookController::class);
    Route::post('book-reconciliation-request-approval/{item}',[Admin\Library\BookReconciliationController::class,'sendBookReconciliationForApproval']);
    Route::apiResource('books-reconciliation', Admin\Library\BookReconciliationController::class);
    Route::apiResource('books-reconciliation-detail', Admin\Library\BookReconciliationDetailController::class);
    Route::post('inventory-reconciliation-request-approval/{item}',[Admin\InventoryReconciliationController::class,'sendInventoryReconciliationForApproval']);
    Route::apiResource('inventory-reconciliation', Admin\InventoryReconciliationController::class);
    Route::apiResource('inventory-reconciliation-detail', Admin\InventoryReconciliationDetailController::class);
    Route::post('book-request-approval/{item}',[Admin\Library\BookRequestController::class,'sendBookRequestForApproval']);
    Route::apiResource('book-request', Admin\Library\BookRequestController::class);
    Route::get('employee-requested-books/{item}', [Admin\Library\BookRequestController::class,'getEmployeeRequestedBooks']);
    Route::get('requested-books-by-req-no/{item}', [Admin\Library\BookRequestController::class,'getRequestedBooksByRequestNo']);

    Route::apiResource('book-issued', Admin\Library\BookIssuedController::class);
    Route::get('employee-issued-books/{item}', [Admin\Library\BookIssuedController::class,'getEmployeeIssuedBooks']);

    Route::get('book-summary', [Admin\Library\BookController::class, 'getBookSummary']);
    Route::get('book-issued-summary/{book}', [Admin\Library\BookController::class, 'getBookIssuedSummary']);

    Route::post('return-book', [Admin\Library\BookIssuedController::class,'returnBooks']);
    Route::get('library-dropdown', [Admin\Library\BookIssuedController::class,'getLibraryDropDown']);

    Route::apiResource('book-variant', Admin\Library\BookVariantController::class);
    //Dispose

    //HR

    //Recruitment
    Route::post('employee-requisition-approval/{item}',[HR\Recruitment\EmployeeRequisitionController::class,'sendEmployeeRequisitionForApproval']);
    Route::get('consultant-recruitment-plan',[HR\Recruitment\RecruitmentPlanController::class,'getConsultantRecruitmentPlan']);
    Route::apiResource('recruitment-plan', HR\Recruitment\RecruitmentPlanController::class);
    Route::apiResource('recruitment-plan-detail', HR\Recruitment\RecruitmentPlanDetailController::class);
    Route::post('add-recruitment-plan-bulk', [HR\Recruitment\RecruitmentPlanDetailController::class,'addRecruitmentPlanBulk']);
    Route::apiResource('consultant-timesheet', HR\Recruitment\ConsultantTimesheetController::class);
    Route::apiResource('consultant-timesheet-detail', HR\Recruitment\ConsultantTimesheetDetailController::class);
    Route::post('consultant-timesheet-approval/{item}',[HR\Recruitment\ConsultantTimesheetController::class,'sendConsultantTimesheetForApproval']);
    Route::get('employee-requisition-dropdown', [HR\Recruitment\EmployeeRequisitionController::class,'empReqDropDown']);
    Route::apiResource('employee-requisition', HR\Recruitment\EmployeeRequisitionController::class);

    Route::apiResource('manage-jobs', HR\Recruitment\ManageJobController::class);
    Route::get('list-applied-jobs/{item}', [HR\Recruitment\ManageJobController::class, 'viewAppliedJobs']);
    Route::get('list-all-candidates', [HR\Recruitment\ManageJobController::class, 'listAllCandidates']);
    Route::get('get-applied-job/{item}', [HR\Recruitment\ManageJobController::class, 'getAppliedJob']);
    Route::get('get-feedback-by-interview-id/{item}', [HR\Recruitment\InterviewQuestionnaireController::class, 'getFeedbackByInterviewId']);
    Route::post('change-candidate-status', [HR\Recruitment\ManageJobController::class, 'changeCandidateStatus']);
    Route::post('update-candidate', [HR\Recruitment\ManageJobController::class, 'updateApplyJob']);
    Route::get('hr-dropdown', [HR\Recruitment\ManageJobController::class,'hrDropdown']);

    Route::get('candidate-online-test/dropdowns', [HR\Recruitment\CandidateOnlineTestController::class,'getDropdowns']);
    Route::get('get-submitted-test-answers/{uuid}', [HR\Recruitment\CandidateOnlineTestController::class,'getTestAnswers']);
    Route::apiResource('candidate-online-test', HR\Recruitment\CandidateOnlineTestController::class);

    Route::apiResource('interview-results', HR\Recruitment\InterviewResultController::class);

    Route::apiResource('interview-committee', HR\Recruitment\InterviewCommitteeController::class);
    Route::post('update-interview-comments/{item}', [HR\Recruitment\InterviewCommitteeController::class,'updateInterviewComments']);
    Route::apiResource('schedule-interview', HR\Recruitment\ScheduleInterviewController::class);
    Route::apiResource('interview-questionnaire', HR\Recruitment\InterviewQuestionnaireController::class);
    Route::apiResource('interview-questions', HR\Recruitment\InterviewQuestionController::class);
    Route::apiResource('question-options', HR\Recruitment\QuestionOptionController::class);
    Route::apiResource('question-answers', HR\Recruitment\QuestionAnswerController::class);
    Route::get('question-answers-apply-job-id', [HR\Recruitment\QuestionAnswerController::class,'getAnswersByApplyJobId']);
    Route::get('question-answers-job-id', [HR\Recruitment\QuestionAnswerController::class,'getAnswersByJobId']);

    Route::post('offer-letter-approval/{item}',[HR\Recruitment\OfferLetterController::class,'sendOfferLetterForApproval']);
    Route::apiResource('offer-letter', HR\Recruitment\OfferLetterController::class);


    //Orientation Plan
    Route::post('add-orientation-activity/{item}', [HR\Recruitment\OrientationPlanController::class, 'addOrientationActivity']);
    Route::post('update-orientation-activity/{item}', [HR\Recruitment\OrientationPlanController::class, 'updateOrientationActivity']);
    Route::get('delete-orientation-activity/{item}', [HR\Recruitment\OrientationPlanController::class, 'deleteOrientationActivity']);
    Route::get('delete-orientation-participant/{item}', [HR\Recruitment\OrientationPlanController::class, 'deleteOrientationParticipant']);
    Route::apiResource('orientation-plan', HR\Recruitment\OrientationPlanController::class);
    Route::get('get-employee-orientation-plan/{item}', [HR\Recruitment\OrientationPlanController::class,'getEmployeeOrientationplan']);


    Route::post('add-workplan-activity', [HR\Recruitment\EmployeeWorkplanController::class, 'addWorkplanActivity']);
    Route::post('update-workplan-activity/{item}', [HR\Recruitment\EmployeeWorkplanController::class, 'updateWorkplanActivity']);
    Route::get('delete-workplan-activity/{item}', [HR\Recruitment\EmployeeWorkplanController::class, 'deleteWorkPlanActivity']);
    Route::get('delete-workplan-participant/{item}', [HR\Recruitment\EmployeeWorkplanController::class, 'deleteWorkplanParticipant']);
    Route::apiResource('employee-workplan', HR\Recruitment\EmployeeWorkplanController::class);
    Route::get('get-employee-workplan/{item}', [HR\Recruitment\EmployeeWorkplanController::class,'getEmployeeWorkplan']);
    Route::get('hr-dashboard-stats', [HR\HrDashboardStatsController::class,'getHrDashboardStats']);

    Route::apiResource('employee-contract', HR\Recruitment\EmployeeContractController::class);
    Route::apiResource('employee-contract-parent', HR\Recruitment\EmployeeContractParentController::class);
    Route::get('get-employee-contract/{item}', [HR\Recruitment\EmployeeContractController::class,'getEmployeeContract']);
    //Configuration
    Route::apiResource('email-template', \App\Http\Controllers\EmailTemplateController::class);
    Route::post('get-email-template-data', [\App\Http\Controllers\EmailTemplateController::class,'getEmailTemplateContent1']);

    Route::apiResource('general-template', Configuration\GeneralTemplatesController::class);
    Route::apiResource('generated-letter', Configuration\GeneratedLetterController::class);
    Route::post('save-system-generated-letter', [Configuration\GeneratedLetterController::class,'saveSystemGeneratedLetter']);
    Route::apiResource('draft-letter', Configuration\DraftLetterController::class);
    Route::post('get-template-data', [Configuration\GeneratedLetterController::class,'getTemplateContent1']);
    Route::get('configuration-dropdown', [Configuration\GeneralTemplatesController::class,'getConfigurationDropdown']);

    //Payscale
    Route::apiResource('payscale', HR\Payscale\PayscaleController::class);
    Route::apiResource('payscale-grading', HR\Payscale\PayscaleGradingController::class);
    Route::post('cola-approval/{item}', [HR\Payscale\SalaryRangeController::class,'sendColaPercentageForApproval']);
    Route::post('apply-cola/{salaryRange}', [HR\Payscale\SalaryRangeController::class,'applyCola']);
    Route::post('add-cola/{salaryRange}', [HR\Payscale\SalaryRangeController::class,'addCola']);
    Route::apiResource('salary-range', HR\Payscale\SalaryRangeController::class);
    Route::get('payscale-dropdown', [HR\Payscale\PayscaleController::class,'payScaleDropdown']);

    //Retirement Benefit

    Route::post('retirement-benefit-approval/{item}', [HR\RetirementBenefitController::class,'sendRetirementBenefitForApproval']);
    Route::apiResource('retirement-benefit', HR\RetirementBenefitController::class);
    Route::apiResource('gratuity-calculation', HR\GratuityCalculationController::class);
    Route::apiResource('gratuity-calculation-detail', HR\GratuityCalculationDetailController::class);
    Route::post('gratuity-calculation-approval/{item}',[HR\GratuityCalculationController::class,'sendGratuityForApproval']);
    Route::get('gratuity-dropdown', [HR\GratuityCalculationController::class,'GrDropdown']);

    //Finance
    //Budget Category
    Route::apiResource('budget-category', Finance\Budget\BudgetCategoryController::class);
    Route::post('annual-budget-approval/{item}',[Finance\Budget\AnnualBudgetController::class,'sendAnnualBudgetForApproval']);
    Route::apiResource('annual-budget', Finance\Budget\AnnualBudgetController::class);
    Route::apiResource('annual-budget-detail', Finance\Budget\AnnualBudgetDetailController::class);
    //Project Budget
    Route::post('project-budget-approval/{item}',[Finance\Budget\ProjectBudgetController::class,'sendProjectBudgetForApproval']);

    Route::apiResource('project-budget', Finance\Budget\ProjectBudgetController::class);
    Route::apiResource('project-budget-detail', Finance\Budget\ProjectBudgetDetailController::class);

    Route::post('project-budget-realign',[Finance\Budget\ProjectBudgetController::class, 'realignProjectBudget']);

    //Budget Dropdown
    Route::get('finance-dropdown', [Finance\ChartOfAccount\ChartOfAccountController::class,'financeDropdown']);
    Route::get('program-budget-dropdown', [Finance\ChartOfAccount\ChartOfAccountController::class,'budgetDropdown']);

    Route::apiResource('finance-bill', Finance\FinanceBill\FinanceBillController::class);
    Route::apiResource('finance-bill-detail', Finance\FinanceBill\FinanceBillDetailController::class);

    Route::apiResource('admin-bill', Finance\AdminInvoice\AdminInvoiceController::class);
    Route::post('admin-bill-approval/{item}',[Finance\AdminInvoice\AdminInvoiceController::class,'sendAdminBillForApproval']);

    //Las Invoice

    Route::post('las-invoice-approval/{item}',[Finance\LasInvoiceController::class,'sendLasInvoiceForApproval']);
    Route::apiResource('las-invoice', Finance\LasInvoiceController::class);
    Route::get('las-invoice-detail-dropdown', [Finance\AdminInvoice\AdminInvoiceController::class,'getDropdown']);
    Route::apiResource('las-invoice-detail', Finance\LasInvoiceDetailController::class);


    //Chart of Account
    Route::apiResource('chart-of-account', Finance\ChartOfAccount\ChartOfAccountController::class);
    Route::get('pending-chart-of-accounts',[Finance\ChartOfAccount\ChartOfAccountController::class,'pendingChartOfAccounts']);
    Route::post('chart-of-account-approval/{item}',[Finance\ChartOfAccount\ChartOfAccountController::class,'sendCOAForApproval']);

    //Head Class
    Route::apiResource('head-class', Finance\ChartOfAccount\HeadClassController::class);
    //Payment Voucher
    Route::apiResource('payment-voucher', Finance\PaymentVoucher\PaymentVoucherController::class);
    Route::apiResource('payment-voucher-detail', Finance\PaymentVoucher\PaymentVoucherDetailController::class);

    //Grants

    //Nofo
    Route::apiResource('nofo', Finance\Grants\NofoController::class);
    Route::apiResource('nofo-detail', Finance\Grants\NofoDetailController::class);

    //Due Delegence
    Route::apiResource('due-delegence', Finance\Grants\DueDelegenceController::class);
    Route::apiResource('due-delegence-detail', Finance\Grants\DueDelegenceDetailController::class);

    //Grant Proposal
    Route::apiResource('grant-proposal', Finance\Grants\GrantProposalController::class);

    //Grant Contract
    Route::apiResource('grant-contract', Finance\Grants\GrantContractController::class);

    //Grant Fund Request
    Route::apiResource('grant-fund-request', Finance\Grants\GrantFundRequestController::class);

    //Grant Financial Report
    Route::apiResource('grant-financial-report', Finance\Grants\GrantFinancialReportController::class);

    //Grant Fund Request
    Route::apiResource('grant-close-out', Finance\Grants\GrantCloseOutController::class);

    //Grant Appreciation Letter
    Route::apiResource('grant-appreciation-letter', Finance\Grants\GrantAppreciationLetterController::class);

    //Grant Log Framework
    Route::apiResource('grant-framework', Finance\Grants\GrantLogFrameworkController::class);

    //Sub Grants
    Route::apiResource('sub-grant', Finance\SubGrants\SubGrantController::class);
//  Due Delegence
    Route::apiResource('sg-due-deligence', Finance\SubGrants\SubGrantDueDeligenceController::class);
    Route::apiResource('sg-due-deligence-detail', Finance\SubGrants\SubGrantDueDeligenceDetailController::class);
// Sub Grant Proposal
    Route::apiResource('sub-grant-proposal', Finance\SubGrants\SubGrantProposalController::class);

    //Sub Grant Log Framework
    Route::apiResource('sub-grant-framework', Finance\SubGrants\SubGrantLogFrameworkController::class);

    //Sub Grant Contract
    Route::apiResource('sub-grant-contract', Finance\SubGrants\SubGrantContractController::class);
    //Sub Grant Fund Request
    Route::apiResource('sub-grant-fund-request', Finance\SubGrants\SubGrantFundRequestController::class);
    //Sub Grant Financial Report
    Route::apiResource('sub-grant-financial-report', Finance\SubGrants\SubGrantFinancialReportController::class);
    //Sub Grant Close Out
    Route::apiResource('sub-grant-close-out', Finance\SubGrants\SubGrantCloseOutController::class);
    //Sub Grant Appreciation
    Route::apiResource('sub-grant-appreciation', Finance\SubGrants\SubGrantAppreciationController::class);

    //Reimbursement

    Route::apiResource('reimbursement', Finance\Reimbursement\ReimbursementController::class);
    Route::get('reimbursement-by-user', [Finance\Reimbursement\ReimbursementController::class,'reimbursementByUser']);

    Route::post('reimbursement-approval/{item}',[Finance\Reimbursement\ReimbursementController::class,'sendReimbursementForApproval']);

    Route::apiResource('reimbursement-expense', Finance\Reimbursement\ReimbursementExpenseController::class);
    Route::apiResource('claim-travel-expense', Finance\ClaimTravelExpenseController::class);
    Route::get('claim-travel-expense-by-user', [Finance\ClaimTravelExpenseController::class,'claimTravelExpenseByUser']);
    Route::apiResource('claim-travel-expense-detail', Finance\ClaimTravelExpenseDetailController::class);
    Route::post('travel-expense-approval/{item}',[Finance\ClaimTravelExpenseController::class,'sendTravelExpenseForApproval']);

    Route::apiResource('court-expense', Finance\CourtExpenseController::class);
    Route::get('court-expense-by-user', [Finance\CourtExpenseController::class,'getUsersCourtExpense']);
    Route::post('court-expense-approval/{item}',[Finance\CourtExpenseController::class,'sendCourtExpenseForApproval']);
    Route::get('expense-dropdown', [Finance\Reimbursement\ReimbursementController::class,'expenseDropdown']);
    //Tax Rate
    Route::apiResource('tax-rate', Finance\TaxRateController::class);


    //Audit
    //Audit Report

    Route::apiResource('audit-report', Finance\Audit\AuditReportController::class);

    //Audit Plan
    Route::post('audit-approval/{item}',[Finance\Audit\AuditPlanController::class,'sendAuditForApproval']);
    Route::apiResource('audit-plan', Finance\Audit\AuditPlanController::class);
    Route::apiResource('audit-plan-report', Finance\Audit\AuditPlanReportController::class);

    // M&E Observation Sheet
    Route::apiResource('observation-sheet', Program\Project\MnE\ObservationSheetController::class);
    Route::apiResource('mne-observations', Program\Project\MnE\MneObservationController::class);
    Route::apiResource('programmatic-response', Program\Project\MnE\ObservationProgrammaticResponseController::class);
    Route::get('observation-dropdown', [Program\Project\MnE\ObservationSheetController::class,'ObservationDropdown']);

    // Project MnE Plan
    Route::get('approved-mne-plan-projects' ,[Program\Project\MnE\ProjectMnePlanController::class, 'approvedMnePlanProjects']);
    Route::get('all-mne-plans', [Program\Project\MnE\ProjectMnePlanController::class, 'getAllMne']);
    Route::get('all-mne-approved-plans', [Program\Project\MnE\ProjectMnePlanController::class, 'getAllApprovedMne']);
    Route::get('mne-plan-dropdowns', [Program\Project\MnE\ProjectMnePlanController::class, 'getMneDropdowns']);
    Route::post('mne-plan-approval/{item}', [Program\Project\MnE\ProjectMnePlanController::class, 'sendMNEPlanForApproval']);

    Route::get('project/{project}/mne-plans/get-dropdowns', [Program\Project\MnE\ProjectMnePlanController::class, 'getDropDowns']);
    Route::apiResource('project.mne-plans', Program\Project\MnE\ProjectMnePlanController::class)->shallow();

    // Project MnE Details
    Route::get('mne-plan-details-by-plan-id/{plan}', [Program\Project\MnE\MnePlanDetailController::class, 'getPlanAllDetails']);
    Route::post('mne-plan-details/save-movs' ,[Program\Project\MnE\MnePlanDetailController::class, 'saveMnePlanDetailMovs']);
    Route::apiResource('mne-plan-details', Program\Project\MnE\MnePlanDetailController::class);

    // Project MnE Plan Goals
    Route::apiResource('mne-plan.mne-plan-goals', Program\Project\MnE\MnePlanGoalController::class)->shallow();

    // Project MnE Plan Outputs
    Route::apiResource('mne-plan.mne-plan-outputs', Program\Project\MnE\MnePlanOutputController::class)->shallow();

    // Project MnE Plan Outcomes
    Route::apiResource('mne-plan.mne-plan-outcomes', Program\Project\MnE\MnePlanOutcomeController::class)->shallow();

    // Project MnE WorkPlan
    Route::post('nme-workplan/{item}/form/{form}/fill-form' ,[Program\Project\MnE\ProjectMneWorkplanController::class, 'fillForm']);
    Route::get('mne-workplan/{item}/form/{form}/responses' ,[Program\Project\MnE\ProjectMneWorkplanController::class, 'getFormResponses']);
    Route::get('approved-progress-workplan-projects' ,[Program\Project\MnE\ProjectMneWorkplanController::class, 'approvedProgressWorkplanProjects']);
    Route::get('project/{project}/mne-workplans/get-dropdowns' ,[Program\Project\MnE\ProjectMneWorkplanController::class, 'getDropdowns']);
    Route::apiResource('project.mne-workplans', Program\Project\MnE\ProjectMneWorkplanController::class)->shallow();

    // Question Forms
    Route::get('questionnaire-forms/get-dropdowns', [Questionnaire\QuestionnaireFormController::class ,'getDropdowns']);
    Route::apiResource('questionnaire-forms', Questionnaire\QuestionnaireFormController::class);

    // Questions
    Route::apiResource('form.questions', Questionnaire\QuestionController::class)->shallow();

    // Questionnaire answers
    Route::post('submit-test-assessment', [Questionnaire\QuestionnaireFormController::class ,'submitTestAssessment']);


    // PDU
    Route::get('indicators-activities/{item}',[PDU\CheckInSheetController::class,'projectByID']);
    Route::apiResource('check-in-sheet',PDU\CheckInSheetController::class);

    // Aproval Process
    Route::get('process-detail/{id}',[ApprovalProcess\ApprovalProcessController::class,'addProcess']);
    Route::apiResource('approval-process', ApprovalProcess\ApprovalProcessController::class);

    // User
    Route::post('search-emp',[Configuration\UserController::class,'searchEmp']);
    Route::get('user-dropdown',[Configuration\UserController::class,'userDropdown']);
    Route::apiResource('user',Configuration\UserController::class);

    // Lesson
    Route::get('lesson-dropdown',[PDU\LessonController::class,'lessonDropdown']);
    Route::get('project-lesson/{id}',[PDU\LessonController::class,'projectLesson']);
    Route::apiResource('lesson', PDU\LessonController::class);

    // Unit
    Route::apiResource('item-unit', Admin\Item\ItemUnitController::class);

    // Item Category
    Route::apiResource('item-category', Admin\Item\ItemCategoryController::class);

    // Item Sub-Category

    Route::apiResource('item-sub-category', Admin\Item\ItemSubCategoryController::class);

    // Add Item

    Route::get('add-item',[Admin\Item\ItemController::class,'addItem']);
    Route::apiResource('item', Admin\Item\ItemController::class);
    Route::post('add-item-variants', [Admin\Item\ItemVariantController::class, 'addVariants']);
    Route::post('issue-item-variant', [Admin\Item\ItemVariantController::class, 'issueVariant']);
    Route::post('dispose-item-variant', [Admin\Item\ItemVariantController::class, 'disposeVariant']);
    Route::post('reclaim-item-variant', [Admin\Item\ItemVariantController::class, 'reclaimVariant']);
    Route::post('item-variant-approval/{item}',[Admin\Item\ItemVariantController::class,'sendItemDisposeForApproval']);
    Route::get('item-variant-pending',[Admin\Item\ItemVariantController::class,'itemVariantPending']);
    Route::get('item-variant-approved',[Admin\Item\ItemVariantController::class,'itemVariantApproved']);
    Route::get('get-remaining-auction-item',[Admin\Item\ItemVariantController::class,'remainingAuctionItems']);
    Route::apiResource('item-variants', Admin\Item\ItemVariantController::class);


    Route::post('item-variant-report',[Finance\ReportController::class,'itemVariantReport']);

    // Purchase Request
    Route::get('get-remaining-pr-items/{id}',[Admin\PurchaseRequest\PurchaseRequestController::class,'getRemainingItems']);
    Route::get('add-purchase-request',[Admin\PurchaseRequest\PurchaseRequestController::class,'addPurchaseRequest']);
    Route::post('get-items',[Admin\PurchaseRequest\PurchaseRequestController::class,'getItems']);
    Route::post('purchase-request-approval/{item}',[Admin\PurchaseRequest\PurchaseRequestController::class,'sendPurchaseRequestForApproval']);
    Route::post('add-purchase-request-item',[Admin\PurchaseRequest\PurchaseRequestController::class,'addPRItems']);
    Route::post('update-purchase-request-item/{item}',[Admin\PurchaseRequest\PurchaseRequestController::class,'updatePRItem']);
    Route::post('delete-purchase-request-item/{item}',[Admin\PurchaseRequest\PurchaseRequestController::class,'deleteItem']);
    Route::post('get-remaining-items',[Admin\PurchaseRequest\PurchaseRequestController::class,'getProcurementPlanItems']);
    Route::apiResource('purchase-request', Admin\PurchaseRequest\PurchaseRequestController::class);
    Route::get('purchase-request-by-user', [Admin\PurchaseRequest\PurchaseRequestController::class,'purchaseRequestByUser']);

    // Dispose Requests
    Route::apiResource('dispose-requests', Admin\DisposeRequest\DisposeRequestController::class);
    Route::get('dispose-request-details/get-dropdowns',[Admin\DisposeRequest\DisposeRequestDetailController::class, 'getDropdowns']);
    Route::get('get-remaining-dr-items/{item}',[Admin\DisposeRequest\DisposeRequestDetailController::class, 'getRemainingItems']);
    Route::apiResource('dispose-request-details', Admin\DisposeRequest\DisposeRequestDetailController::class);

    Route::get('project/{project_id}/activity-calendars/get-dropdowns',[Program\Project\ActivityCalendarController::class, 'getDropdowns']);
    Route::get('project-activity-calendars/{project_id}',[Program\Project\ActivityCalendarController::class, 'getProjectActivityCalender']);
    Route::apiResource('project.activity-calendars', Program\Project\ActivityCalendarController::class)->shallow();

    // Designations
    Route::post('employee-organogram',[DesginationController::class,'getOrganogram']);
    Route::apiResource('designations', DesginationController::class);

    // Air Travel Request
    Route::get('atr-vendor', [Admin\VendorController::class,'vendorATRList']);
    Route::post('save-atr-vendor/{atr}', [Admin\AirTravelRequestController::class,'attachATRVendor']);
    Route::post('accept-quotation/{atr}', [Admin\AirTravelRequestController::class,'acceptQuotation']);
    Route::get('air-travel-requests-dropdowns', [Admin\AirTravelRequestController::class,'getDropdowns']);
    Route::post('air-travel-request-approval/{item}',[Admin\AirTravelRequestController::class,'sendAirTravelRequestForApproval']);
    Route::apiResource('air-travel-requests', Admin\AirTravelRequestController::class);

    Route::post('upload-document/{atr}', [Admin\AirTravelRequestDetailController::class,'addBoardingDocument']);
    Route::apiResource('atr-items', Admin\AirTravelRequestDetailController::class);
    // Air Travel Request Quotation
    Route::get('get-atr-quotation/{atr}', [Admin\VendorAtrQuotationController::class,'getAtrQuotationList']);
    Route::apiResource('atr-quotation', Admin\VendorAtrQuotationController::class);

    Route::post('save-vehicle-maintenance-vendor/{vm}', [Admin\VehicleMaintenanceFormController::class,'attachVMVendor']);
    Route::post('maintenance-request-approval/{item}',[Admin\VehicleMaintenanceFormController::class,'sendMaintenanceRequestForApproval']);
    Route::apiResource('vehicle-maintenance-forms', Admin\VehicleMaintenanceFormController::class);
    Route::apiResource('vehicle-maintenance-details', Admin\VehicleMaintenanceDetailController::class);
    Route::apiResource('rfq-types', Admin\RfqTypeController::class);

    // Vehicle Maintenance Request Quotation
    Route::post('accept-vm-quotation/{vm}', [Admin\VehicleMaintenanceFormController::class,'acceptVMQuotation']);
    Route::get('get-vm-quotation/{vm}', [Admin\VendorVehMaintenanceQuotController::class,'getVMQuotationList']);
    Route::apiResource('vm-quotation', Admin\VendorVehMaintenanceQuotController::class);

    Route::post('purchase-request-rfqs-item-add', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'addPrItem']);
    Route::post('save-rfq-vendor/{purchaseRequestRfq}', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'attachRFQVendor']);
    Route::post('purchase-request-rfqs-item-update', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'updatePrItem']);
    Route::delete('purchase-request-rfqs-item-delete/{item}', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'deletePrItem']);
    Route::get('rfq-dropdown', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'rfqDropdown']);
    Route::post('rfq-approval/{item}', [Admin\PurchaseRequest\PurchaseRequestRfqController::class,'sendRFQForApproval']);
    Route::get('get-rfqs-items-by-id/{item}', [Admin\PurchaseRequest\PurchaseRequestRfqController::class , 'getRfqsItems']);
    Route::post('update-rfq-expiry/{item}',[Admin\PurchaseRequest\PurchaseRequestRfqController::class,'updateRfqExpiry']);
    Route::apiResource('purchase-request-rfqs', Admin\PurchaseRequest\PurchaseRequestRfqController::class);

    // Research Matrix Plan
    Route::get('rm-plans/dropdown', [Program\Rdu\RmPlanController::class,'getRMDropDown']);
    Route::get('rm-plans/get-data-sources-by-rm-plan/{item}', [Program\Rdu\RmPlanController::class,'getRmDataSources']);
    Route::post('research-matrix-plan-approval/{item}',[Program\Rdu\RmPlanController::class,'sendRMPlanForApproval']);
    Route::apiResource('rm-plans', Program\Rdu\RmPlanController::class);
    Route::apiResource('rm-plan-data-sources', Program\Rdu\RmPlanDataSourceController::class);

    Route::get('rmp-methodology-details/dropdown', [Program\Rdu\RmPlanController::class,'getRMDropDown']);
    Route::get('get-methodology-details-by-rmp-id/{item}', [Program\Rdu\RmpMethodologyDetailController::class,'getMethodologyDetailsByRMPId']);
    Route::apiResource('rmp-methodology-details', Program\Rdu\RmpMethodologyDetailController::class);

    // Tender
    Route::get('tender/get-dropdowns', [Admin\TenderController::class, 'getDropdowns']);
    Route::post('tender-item-add', [Admin\TenderController::class, 'addItem']);
    Route::post('tender-item-update', [Admin\TenderController::class, 'updateItem']);
    Route::post('tender-item-delete/{item}', [Admin\TenderController::class, 'deleteItem']);
    Route::post('tender-approval/{item}', [Admin\TenderController::class, 'sendTenderForApproval']);
    Route::post('tender-publish/{tender}', [Admin\TenderController::class, 'floatTender']);
    Route::post('upload-tender-pack-document',[Admin\TenderController::class,'uploadAttachment']);
    Route::post('update-tender-expiry/{tender}',[Admin\TenderController::class,'updateTenderExpiry']);
    Route::apiResource('tenders', Admin\TenderController::class);

    // Tender MoM
    Route::get('tender-mom-dropdown',[Admin\TenderMinutesOfMeetingController::class,'momDropDown']);
    Route::apiResource('tender-mom',Admin\TenderMinutesOfMeetingController::class);

    // Inventory
    Route::get('inventories/get-dropdowns', [Admin\InventoryController::class, 'getDropdowns']);
    Route::post('inventories/idle-approval/{item}', [Admin\InventoryController::class, 'sendForApproval']);
    Route::post('inventories/idle-action', [Admin\InventoryController::class, 'idleAction']);
    Route::get('inventories/auction-list', [Admin\InventoryController::class, 'getAuction']);
    Route::get('inventories/approved-idles', [Admin\InventoryController::class, 'getApprovedIdles']);
    Route::apiResource('inventories', Admin\InventoryController::class);
    Route::apiResource('dispose-items', Admin\DisposeItemController::class);

    // GatePass
    Route::post('gate-pass-approval/{item}',[Admin\AuctionGatePassController::class,'sendGatePassForApproval']);
    Route::apiResource('auction-gate-passes', Admin\AuctionGatePassController::class);

    //Stock Request
    Route::post('stock-request-approval/{item}',[Admin\StockRequestController::class,'sendStockRequestForApproval']);
    Route::apiResource('stock-request', Admin\StockRequestController::class);
    Route::apiResource('stock-request-detail', Admin\StockRequestDetailController::class);

    Route::apiResource('issue-stock', Admin\IssueStockController::class);
    Route::apiResource('issue-stock-detail', Admin\IssueStockDetailController::class);

    Route::apiResource('stock-transfer-note', Admin\StockTransferNoteController::class);
    Route::apiResource('stock-receive-note', Admin\StockReceiveNoteController::class);


    Route::post('get-item-variants-item-id', [Admin\IssueStockController::class,'getItemVariantsByItemId']);

    Route::get('stock-request-dropdown', [Admin\StockRequestDetailController::class,'getDropdown']);



    // Shifts
    Route::get('shift-dropdown', [Configuration\ShiftController::class, 'shiftDropdown']);
    Route::apiResource('shift',Configuration\ShiftController::class);

    // Documents
    Route::apiResource('document',Configuration\DocumentsController::class);

    // Procurement Plan
    Route::post('procurement-plan-approval/{procurement}', [Admin\ProcurementController::class, 'sendProcurementPlanForApproval']);
    Route::get('procurements/get-dropdowns', [Admin\ProcurementController::class, 'getDropDowns']);
    Route::delete('delete-procurement-item/{item}', [Admin\ProcurementController::class, 'deleteItem']);
    Route::post('add-procurement-item', [Admin\ProcurementController::class, 'addItem']);
    Route::post('update-procurement-item/{item}', [Admin\ProcurementController::class, 'updateItem']);
    Route::post('program-budget-heads', [Admin\ProcurementController::class, 'getHeadByProgramBudget']);
    Route::get('get-procurement-item/{item}', [Admin\ProcurementController::class, 'getItem']);

    Route::apiResource('procurements',Admin\ProcurementController::class);

    // Vendor Documents
    Route::apiResource('vendor-documents',Admin\VendorDocumentsController::class);
    Route::post('change-vendor-password',[Auth\VendorLoginController::class,'chnageVendorPassword']);
    Route::post('change-employee-password',[Configuration\EmployeeController::class,'chnagePassword']);

    // Vendor Quotation
    Route::post('upload-bidding-documents/{rfq}',[Admin\VendorQuotationController::class,'uploadBiddingDocuments']);
    Route::post('upload-tender-bidding-documents/{tender}',[Admin\VendorQuotationController::class,'uploadTenderBiddingDocuments']);
    Route::post('apply/{quotation}',[Admin\VendorQuotationController::class,'applyProject']);
    Route::post('save-tender-quotation',[Admin\VendorQuotationController::class,'saveTenderQuotation']);
    Route::post('update-tender-quotation/{vendorQuotation}',[Admin\VendorQuotationController::class,'updateTenderQuotation']);
    Route::get('view-quotation/{id}',[Admin\VendorQuotationController::class,'ViewQuotation']);
    Route::get('view-tender-quotation/{id}',[Admin\VendorQuotationController::class,'ViewTenderQuotation']);
    Route::post('decode-amount',[Admin\VendorQuotationController::class,'decodeAmount']);
    Route::apiResource('vendor-quotation',Admin\VendorQuotationController::class);

    Route::get('remaining-pr',[Admin\PurchaseRequest\PurchaseRequestRfqController::class,'remainingPr']);

    // Asset
    Route::get('assets/get-dropdowns',[Admin\Asset\AssetController::class,'getDropdowns']);
    Route::apiResource('assets',Admin\Asset\AssetController::class);

    //Locations
    Route::apiResource('locations',Admin\LocationController::class);
    Route::get('get-all-districts', [Admin\LocationController::class, 'getDistricts']);

    // Comparative

    Route::get('projects',[Admin\ComparativeController::class,'rfqProjects']);
    Route::get('projects-quotation/{id}',[Admin\ComparativeController::class,'getProjectQuotation']);
    Route::get('get-responsive-quotation/{id}',[Admin\ComparativeController::class,'getResponsiveQuotation']);
    Route::post('short-list-vendors',[Admin\ComparativeController::class,'shortListVendors']);
    Route::post('generate-comparative/{item}',[Admin\ComparativeController::class,'generateComparative']);
    Route::post('project-awarded',[Admin\ComparativeController::class,'projectAwarded']);

    // Comparative Tender
    Route::get('tender-projects',[Admin\ComparativeController::class,'tenderProjects']);
    Route::get('tender-quotation/{id}',[Admin\ComparativeController::class,'getTenderQuotation']);
    Route::post('generate-tender-comparative/{item}',[Admin\ComparativeController::class,'generateTenderComparative']);
    Route::get('get-tender-responsive-quotation/{id}',[Admin\ComparativeController::class,'getTenderResponsiveQuotation']);
    Route::post('tender-project-awarded',[Admin\ComparativeController::class,'tenderAwarded']);
    // Purchase Order
    Route::get('get-awarded-projects',[Admin\PurchaseOrder\PurchaseOrderController::class,'getAwardedProjects']);
    Route::get('get-awarded-tender-projects',[Admin\PurchaseOrder\PurchaseOrderController::class,'getAwardedTenderProjects']);
    Route::post('purchase-order/vendor-acknowledged',[Admin\PurchaseOrder\PurchaseOrderController::class,'vendorAcknowledged']);
    Route::apiResource('purchase-order',Admin\PurchaseOrder\PurchaseOrderController::class);

    // GRN
    Route::post('grn-request-approval/{item}', [Admin\GRN\GRNController::class, 'sendGrnRequestForApproval']);
    Route::post('add-grn-item',[Admin\GRN\GRNController::class,'addGrnItem']);
    Route::post('update-grn-item',[Admin\GRN\GRNController::class,'updateGrnItem']);
    Route::post('grn-approval/{item}',[Admin\GRN\GRNController::class,'sendGrnForApproval']);
    Route::get('view-grn/{id}',[Admin\GRN\GRNController::class,'viewGRN']);
    Route::get('grn-approval-list',[Admin\GRN\GRNController::class,'grnApprovalList']);
    Route::post('approve-grn',[Admin\GRN\GRNController::class,'approveGrn']);
    Route::get('get-remaining-po-items/{po_id}',[Admin\GRN\GRNController::class,'getRemainingPoItems']);
    Route::apiResource('grn',Admin\GRN\GRNController::class);

    // Invoice

    Route::post('pending-invoices',[Admin\Invoice\InvoiceController::class,'getPendingInvoices']);
    Route::post('payroll-voucher',[Admin\Invoice\InvoiceController::class,'createPayrollVoucher']);
    Route::get('get-approved-grn-list',[Admin\Invoice\InvoiceController::class,'getApprovedGrnList']);
    Route::post('vendor-invoices',[Admin\Invoice\InvoiceController::class,'vendorInvoices']);
    Route::post('save-invoice',[Admin\Invoice\InvoiceController::class, 'saveInvoice']);
    Route::post('create-auction-invoice',[Admin\Invoice\InvoiceController::class, 'createAuctionInvoice']);
    Route::apiResource('invoice',Admin\Invoice\InvoiceController::class);

    Route::get('show-invoice/{item}',[Admin\Invoice\InvoiceController::class,'showInvoice']);


    // Tender Committee
    Route::get('tender-committees/get-dropdowns',[Admin\TenderCommitteeController::class,'getDropdowns']);
    Route::apiResource('tender-committees',Admin\TenderCommitteeController::class);

    // Work Order
    Route::post('work-order/vendor-acknowledged',[Admin\WorkOrder\WorkOrderController::class,'vendorAcknowledged']);
    Route::apiResource('work-order',Admin\WorkOrder\WorkOrderController::class);

    // consultant_contract
    Route::apiResource('consultant-contracts',Admin\ConsultantContract\ConsultantContractController::class);

    // Employee Insurances
    Route::get('employee-insurances/get-dropdowns',[HR\Insurance\EmployeeInsurancesController::class,'getDropdowns']);
    Route::post('employee-insurances/send-for-approval/{item}',[HR\Insurance\EmployeeInsurancesController::class,'sendForApproval']);
    Route::apiResource('employee-insurances',HR\Insurance\EmployeeInsurancesController::class);
    Route::apiResource('employee-relatives',HR\Insurance\EmployeeRelativeController::class);
    Route::apiResource('employee-claim-reimbursements',HR\Insurance\EmployeeClaimReimbursementController::class);

    // HR Leave
    Route::get('leave-dropdown',[HR\Leaves\YearlyLeaveEntitlementController::class,'leaveDropDown']);
    Route::apiResource('leave-entitlement',HR\Leaves\YearlyLeaveEntitlementController::class);

    // HR Employee Leave
    Route::post('leave-approval/{leave}',[HR\Leaves\EmployeeLeaveController::class,'sendLeaveRequestForApproval']);
    Route::get('employee-leave-dropdown',[HR\Leaves\EmployeeLeaveController::class,'employeeLeaveDropDown']);
    Route::get('leave-listing',[HR\Leaves\EmployeeLeaveController::class,'leaveListing']);
    Route::apiResource('leave',HR\Leaves\EmployeeLeaveController::class);

    // Policy
    Route::get('policies-dropdown',[HR\PolicyController::class,'getDropdowns']);
    Route::post('upload-policy-attachment',[HR\PolicyController::class,'uploadAttachment']);
    Route::post('policy-approval/{item}',[HR\PolicyController::class,'sendPolicyForApproval']);
    Route::apiResource('policies',HR\PolicyController::class);

    // Appraisal
    Route::post('performance-approval/{item}',[HR\Appraisal\PerformancePlanningController::class,'sendPerformaceForApproval']);
    Route::get('performance-plannings-dropdowns', [HR\Appraisal\PerformancePlanningController::class, 'getDropdowns']);
    Route::post('sync-employee-performance-kpis', [HR\Appraisal\PerformancePlanningController::class, 'addEmployeeKpis']);
    Route::apiResource('performance-plannings', HR\Appraisal\PerformancePlanningController::class);
    Route::apiResource('key-responsibilities', HR\Appraisal\KeyResponsibilityController::class);
    Route::apiResource('performance-factors', HR\Appraisal\PerformanceFactorController::class);

    Route::apiResource('performance-kpi', HR\Appraisal\AppriasalKpiController::class);
    Route::get('get-indicators-by-kpi-id/{kpiId}', [HR\Appraisal\AppriasalKpiController::class, 'getIndicators']);
    Route::get('get-kpis-by-designation-id/{designationId}', [HR\Appraisal\AppriasalKpiController::class, 'getKpisByDesignation']);

    Route::apiResource('kpi-indicators-mappings', HR\Appraisal\KpiIndicatorsMappingController::class);

    Route::apiResource('departmental-objectives', HR\Appraisal\DepartmentalObjectiveController::class);
    Route::get('get-kpis-by-objective-id/{objectiveId}', [HR\Appraisal\DepartmentalObjectiveController::class, 'getKpis']);

    Route::apiResource('section-questions', HR\Appraisal\SectionQuestionController::class);
    Route::get('kpi-indicators-mappings-by-indicator-id/{indicatorId}', [HR\Appraisal\SectionQuestionController::class, 'getKpiIndicatorsMappings']);

    Route::apiResource('development-goals', HR\Appraisal\DevelopmentGoalController::class);
    Route::apiResource('performance-check-ins', HR\Appraisal\PerformanceCheckInsController::class);
    Route::apiResource('scheduled-check-ins', HR\Appraisal\ScheduledCheckInsController::class);

    Route::post('check-leave-balance',[HR\Leaves\EmployeeLeaveController::class,'getLeaveBalance']);
    Route::apiResource('leave',HR\Leaves\EmployeeLeaveController::class);

    // HR Complaint
    Route::post('committee-action-report/{item}',[HR\Complaint\ComplaintController::class,'actionReport']);
    Route::get('complaint-dropdown',[HR\Complaint\ComplaintController::class,'complaintDropDown']);
    Route::post('complaint-request-approval/{item}',[HR\Complaint\ComplaintController::class,'sendComplaintRequestForApproval']);
    Route::post('complaint-status/{item}',[HR\Complaint\ComplaintController::class,'complaintSendToHr']);
    Route::post('complaint-action/{item}',[HR\Complaint\ComplaintController::class,'complaintAction']);
    Route::post('committee-members/{item}',[HR\Complaint\ComplaintController::class,'createCommittee']);
    Route::post('save-nda-agreement',[HR\Complaint\ComplaintController::class,'saveNdaAgreement']);
    Route::post('update-committee-members/{item}',[HR\Complaint\ComplaintController::class,'updateCommittee']);
    Route::post('submit-complaint-feedback/{item}',[HR\Complaint\ComplaintController::class,'submitComplaintFeedback']);
    Route::get('get-committee-members/{item}',[HR\Complaint\ComplaintController::class,'getCommitteeMembers']);
    Route::get('get-assigned-complaints/{item}',[HR\Complaint\ComplaintController::class,'getAssignedComplaints']);
    Route::apiResource('complaint',HR\Complaint\ComplaintController::class);
    Route::get('employee-complaint/{item}',[HR\Complaint\ComplaintController::class,'getEmployeeComplaints']);


    // GDN

    Route::post('add-gdn-item',[Admin\GDN\GdnController::class,'addGdnItem']);
    Route::post('update-gdn-item',[Admin\GDN\GdnController::class,'updateGdnItem']);
    Route::post('gdn-approval/{item}',[Admin\GDN\GdnController::class,'sendGdnForApproval']);
    Route::get('gdn-approval-list',[Admin\GDN\GdnController::class,'gdnApprovalList']);
    Route::post('approve-gdn',[Admin\GDN\GdnController::class,'approveGdn']);

    Route::apiResource('gdn',Admin\GDN\GdnController::class);

    // Advance Salary
    Route::get('advance-salaries-dropdowns',[HR\AdvanceSalary\AdvanceSalaryController::class, 'getDropdowns']);
    Route::apiResource('advance-salaries',HR\AdvanceSalary\AdvanceSalaryController::class);
    Route::get('employee-advance-salaries/{item}',[HR\AdvanceSalary\AdvanceSalaryController::class,'getEmployeeAdvanceSalaries']);
    Route::get('financial-years-dropdowns', [Admin\FinancialYearController::class, 'getDropdowns']);
    Route::post('advance-salary-approval/{item}',[HR\AdvanceSalary\AdvanceSalaryController::class,'sendAdvanceSalaryRequestForApproval']);
    Route::apiResource('financial-years',Admin\FinancialYearController::class);

    // Position Wise Allowance & Deduction
    Route::get('position-wise-dropdown',[Configuration\PositionWiseAllowDeductController::class,'positionWiseDropDown']);
    Route::apiResource('pw-allowance-deduction',Configuration\PositionWiseAllowDeductController::class);
    // Payroll Tax Rate
    Route::get('payroll-tax-rate-dropdown',[HR\Payroll\PayrollTaxRatesController::class,'payrollTaxRateDropDown']);
    Route::post('tax-rate-listing-fiscalYear',[HR\Payroll\PayrollTaxRatesController::class,'payrollTaxRateByFiscalYear']);
    Route::apiResource('payroll-tax-rates',HR\Payroll\PayrollTaxRatesController::class);

    // Employee Salary Settings
    Route::get('emp-salary-setup-dropdown',[HR\Payroll\EmployeeSalarySetupController::class,'salarySetupDropDown']);
    Route::post('employee-projects',[HR\Payroll\EmployeeSalarySetupController::class,'getEmployeeProjects']);
    Route::apiResource('emp-salary-setups',HR\Payroll\EmployeeSalarySetupController::class);

    // Employee Wise Allowance & Deduction
    Route::get('emp-allow-deduct-dropdown',[HR\Payroll\EmployeeAllowanceDeductionController::class,'employeeWiseDropDown']);
    Route::apiResource('ew-allowance-deduction',HR\Payroll\EmployeeAllowanceDeductionController::class);

    // Allowance & Deduction
    Route::get('allowance-dropdown',[Configuration\AllowanceDeductionController::class,'allowanceDropDown']);
    Route::apiResource('allowance-deduction',Configuration\AllowanceDeductionController::class);

    // Payroll

    Route::post('payroll-approval/{item}',[HR\Payroll\EmployeePayrollMasterController::class,'sendPayrollForApproval']);
    Route::get('payroll-dropdown',[HR\Payroll\EmployeePayrollMasterController::class,'payrollDropDown']);
    Route::post('generate-payroll',[HR\Payroll\EmployeePayrollMasterController::class,'generatePayroll']);
    Route::post('create-payroll-bulk',[HR\Payroll\EmployeePayrollMasterController::class,'createPayrollBulk']);
    Route::post('create-payroll-single',[HR\Payroll\EmployeePayrollMasterController::class,'createPayrollSingle']);
    Route::post('payroll-check',[HR\Payroll\EmployeePayrollMasterController::class,'getEmployeePayroll']);
    Route::post('emp-payroll-check',[HR\Payroll\EmployeePayrollMasterController::class,'payrollCheck']);
    Route::post('delete-payroll',[HR\Payroll\EmployeePayrollMasterController::class,'deletePayroll']);
    Route::apiResource('payroll',HR\Payroll\EmployeePayrollMasterController::class);

    // Governance
    // Agenda
    Route::get('agenda-dropdown',[Governance\BoardMeetingAgendaController::class,'agendaDropDown']);
    Route::post('agenda-approval/{item}', [Governance\BoardMeetingAgendaController::class, 'sendAgendaForApproval']);
    Route::apiResource('agenda',Governance\BoardMeetingAgendaController::class);

    // Board Meeting
    Route::get('board-meeting-dropdown',[Governance\BoardMeetingController::class,'boardMeetingDropDown']);
    Route::post('board-meeting-approval/{item}', [Governance\BoardMeetingController::class, 'sendBoardMeetingForApproval']);

    Route::apiResource('board-meeting',Governance\BoardMeetingController::class);

    // MoM
    Route::get('mom-dropdown',[Governance\MinuteOfMeetingController::class,'momDropDown']);
    Route::post('minutes-of-meeting-approval/{item}', [Governance\MinuteOfMeetingController::class, 'sendMinutesOfMeetingForApproval']);
    Route::post('approve-mom/{item}', [Governance\MinuteOfMeetingController::class, 'approveMinutesOfMeeting']);
    Route::apiResource('minute-of-meeting',Governance\MinuteOfMeetingController::class);

    // Board Resolution Passed
    Route::post('board-resolution-approval/{item}', [Governance\BoardResolutionPassedController::class, 'sendBoardResolutionForApproval']);
    Route::post('approve-resolution/{item}', [Governance\BoardResolutionPassedController::class, 'approveResolution']);
    Route::apiResource('board-resolution-passed',Governance\BoardResolutionPassedController::class);

    // Article of Association
    Route::apiResource('article-of-association',Governance\ArticleOfAssociationController::class);

    // Memorandum
    Route::apiResource('memorandum',Governance\MemorandumController::class);

    // Communication
    Route::apiResource('event-categories', Communication\EventCategoryController::class);
    Route::apiResource('event-sub-categories', Communication\EventSubCategoryController::class);
    Route::post('communication-events-approval/{item}',[Communication\CommunicationEventController::class,'sendCommunicationEventForApproval']);

    Route::apiResource('communication-events', Communication\CommunicationEventController::class);

    Route::apiResource('communication-event-details', Communication\CommunicationEventDetailController::class);
    Route::apiResource('communication-comments', Communication\CommunicationCommentController::class);
    Route::apiResource('team-communication-comments', Communication\TeamCommunicationCommentController::class);
    Route::get('communication-events-dropdowns', [Communication\CommunicationEventController::class, 'getDropdowns']);
    Route::apiResource('assign-communication-event-tasks', Communication\AssignCommunicationEventTaskController::class);
    Route::apiResource('emplyoee-communication-event', Communication\EmployeeCommunicationEventController::class);
    Route::apiResource('communication-event-histories', Communication\CommunicationEventHistoryController::class);

    Route::get('communication-events-stats', [Communication\CommunicationEventController::class, 'eventStats']);


    // Employee Change Log
    Route::post('change-log-dropdown',[Configuration\EmployeeChangeLogController::class,'employeeChangeLogDropDown']);
    Route::apiResource('change-log',Configuration\EmployeeChangeLogController::class);

	// Donor
    Route::apiResource('donor-profile',Donar\DonarProfileController::class);

    // Vouchers
    Route::get('voucher-dropdown',[Finance\Voucher\VoucherController::class,'voucherDropDown']);
    Route::post('journal-voucher',[Finance\Voucher\VoucherController::class,'createJournalVoucher']);
    Route::post('voucher-approval/{item}',[Finance\Voucher\VoucherController::class,'sendVoucherForApproval']);
    Route::get('pending-audit-vouchers',[Finance\Voucher\VoucherController::class,'getPendingVouchers']);
    Route::get('pending-posted-vouchers',[Finance\Voucher\VoucherController::class,'getPendingPostedVouchers']);
    Route::post('verified-voucher/{voucher}',[Finance\Voucher\VoucherController::class,'verifiedVoucher']);
    Route::post('posted-voucher/{voucher}',[Finance\Voucher\VoucherController::class,'postedVoucher']);
    Route::apiResource('voucher',Finance\Voucher\VoucherController::class);

    Route::apiResource('create-journal-voucher',Finance\Voucher\JournalVoucherController::class);

    //Financial Analysis And Decision Making
    Route::apiResource('financial-analysis-worksheet',Finance\FinancialAnalysis\WorksheetController::class);
    Route::apiResource('result-document',Finance\FinancialAnalysis\ResultDocumentController::class);
    Route::apiResource('management-report',Finance\FinancialAnalysis\ManagementReportController::class);

    // Governance Report
    Route::post('governance-report',[Governance\BoardMeetingController::class,'governanceReport']);

    // Employee Timesheet
    Route::post('timesheet-dropdown',[HR\TimeSheet\EmployeeTimesheetController::class,'timeSheetDropDown']);
    Route::post('timesheet-approval/{item}',[HR\TimeSheet\EmployeeTimesheetController::class,'sendEmployeeTimeSheetForApproval']);
    Route::apiResource('timesheet',HR\TimeSheet\EmployeeTimesheetController::class);

    // Salary Accounts Head Settings
    Route::get('salary-head-dropdown',[Finance\SalaryAccountHeadSettingController::class,'salaryAccountHeadDropdown']);

    Route::apiResource('salary-account-head',Finance\SalaryAccountHeadSettingController::class);

    // Salary Accounts Head Settings
    Route::get('salary-account-config-dropdown',[Finance\SalaryAccountConfigurationController::class,'salaryAccountConfigDropdown']);

    Route::apiResource('salary-account-config',Finance\SalaryAccountConfigurationController::class);



    // Finance Reporting
    Route::post('trial-balance',[Finance\ReportController::class,'getTrialBalance']);
    Route::post('income-expense',[Finance\ReportController::class,'getIncomeExpense']);
    Route::post('balance-sheet',[Finance\ReportController::class,'getBalanceSheet']);
    Route::post('general-ledger',[Finance\ReportController::class,'getGeneralLedger']);
    Route::post('general-journal',[Finance\ReportController::class,'getGeneralJournalReport']);
    Route::post('payable-receivable',[Finance\ReportController::class,'getPayableReceivable']);

    Route::post('finance_payable_receivable',[Finance\ReportController::class,'getPayableAndReceivableReport']);
    Route::post('budget_variance_report',[Finance\ReportController::class,'BudgetVarianceReport']);


    // Attendance Report

    //Las configuration
    Route::apiResource('las-configuration',Finance\LasConfigurationController::class);

    //Bank Information
    Route::apiResource('bank-info',Finance\BankInfoController::class);

    //Customer Hub
    Route::get('customer-dropdown',[Finance\CustomerHubController::class,'getCustomerList']);
    Route::apiResource('customer',Finance\CustomerHubController::class);

    //Budget Estimate
    Route::get('estimate-dropdown',[Finance\Estimate\BudgetEstimateController::class,'getBudgetEstimateDropdown']);
    Route::apiResource('budget-estimate',Finance\Estimate\BudgetEstimateController::class);


    //Customer Invoice
    Route::get('customer-invoice-dropdown',[Finance\CustomerInvoice\CustomerInvoiceController::class,'getCustomerInvoiceDropdown']);
    Route::post('customer-estimate',[Finance\CustomerInvoice\CustomerInvoiceController::class,'getCustomerEstimate']);
    Route::apiResource('customer-invoice',Finance\CustomerInvoice\CustomerInvoiceController::class);


    //Employee Reports

    Route::post('active-employee-report',[HR\Reports\EmployeeReportController::class,'activeEmployeeReport']);
    Route::post('new-employee-report',[HR\Reports\EmployeeReportController::class,'newEmployeeReport']);
    Route::post('resigned-employee-report',[HR\Reports\EmployeeReportController::class,'resignedEmployeeReport']);
    Route::post('district-employee-report',[HR\Reports\EmployeeReportController::class,'districtEmployeeReport']);
    Route::post('department-employee-report',[HR\Reports\EmployeeReportController::class,'departmentEmployeeReport']);
    Route::post('gender-employee-report',[HR\Reports\EmployeeReportController::class,'genderCountEmployeeReport']);
    Route::post('employee-details-report',[HR\Reports\EmployeeReportController::class,'employeeDetailReport']);
    Route::post('exit-employee-report',[HR\Reports\EmployeeReportController::class,'exitEmployeeReport']);

    Route::post('exit-employee',[Configuration\EmployeeController::class,'storeExitEmployeeDetails']);
    Route::apiResource('employee-documents',Configuration\EmployeeDocumentController::class);
    Route::apiResource('employee-eobi',Configuration\EmployeeEobiController::class);
    //Exit Employee

    Route::post('off-boarding-approval/{item}',[Configuration\EmployeeOffboardingController::class,'sendEmployeeOffBoardingForApproval']);
    Route::apiResource('employee-off-boarding',Configuration\EmployeeOffboardingController::class);

    Route::apiResource('employee-exit-interview',Configuration\ExitEmployeeInterviewController::class);

    //Special Holiday
    Route::get('special-holiday-dropdown',[HR\Leaves\SpecialHolidayController::class,'specialHolidayDropDown']);
    Route::apiResource('special-holiday', HR\Leaves\SpecialHolidayController::class);

    //Leave Balance
    Route::get('leave-balance-dropdown',[HR\Leaves\LeaveBalanceController::class,'leaveBalanceDropDown']);
    Route::apiResource('leave-balance', HR\Leaves\LeaveBalanceController::class);

    // Attendance Report

    Route::post('daily-attendance',[HR\AttendanceController::class,'employeeDailyAttendanceReport']);
    Route::post('emp-monthly-attendance',[HR\AttendanceController::class,'employeeAttendance']);
    Route::post('monthly-attendance',[HR\AttendanceController::class,'monhtlyAttendance']);
    Route::post('update-attendance',[HR\AttendanceController::class,'updateAttendance']);
    Route::post('manual-attendance',[HR\AttendanceController::class,'addManualAttendance']);
    Route::post('update-manual-attendance',[HR\AttendanceController::class,'updateManualAttendance']);
    Route::get('manual-attendance-dropdown',[HR\AttendanceController::class,'manualAttendanceDropDown']);
    Route::post('manual-attendance-listing',[HR\AttendanceController::class,'manualAttendanceListing']);
    Route::post('manual-att-approval/{item}',[HR\AttendanceController::class,'sendManualAttendanceForApproval']);

    //Clearance Experience Certificate
    Route::get('clearance-exp-dropdown',[Configuration\ClearanceExpCertificateController::class,'clearanceCertificateDropDown']);
    Route::apiResource('clearance-exp-certificate', Configuration\ClearanceExpCertificateController::class);

    // Leave Add Deduct

    Route::get('leave-add-deduct-dropdown',[HR\Leaves\LeaveAddDeductController::class,'addDeductDropDown']);
    Route::apiResource('leave-add-deduct', HR\Leaves\LeaveAddDeductController::class);
    // Pre Gross salary Allowances
    Route::get('pre-gross-salary-allowance-dropdown',[HR\PreGrossSalaryAllowancesController::class,'grossSalaryDropDown']);
    Route::apiResource('pre-gross-salary-allowance', HR\PreGrossSalaryAllowancesController::class);

    // Risk Management
    Route::get('risk-management-dropdowns',[Admin\RiskManagement\RiskRegisterController::class, 'getDropdowns']);
    Route::apiResource('risk-registers',Admin\RiskManagement\RiskRegisterController::class);
    Route::apiResource('risk-register-details',Admin\RiskManagement\RiskRegisterDetailController::class);
    Route::apiResource('risk-quarterly-assessments',Admin\RiskManagement\RiskQuarterlyAssessmentController::class);

    // Jadwa ERP Apis //

    // Company Management

    Route::get('company-dropdown',[Company\CompanyController::class, 'getCompanyDropdowns']);
    Route::apiResource('company',Company\CompanyController::class);

    // Customer

    Route::get('erp-customer-dropdown',[Customer\CustomerController::class, 'getCustomerDropdowns']);
    Route::post('save-attachment',[Customer\CustomerController::class, 'saveAttachment']);
    Route::post('delete-attachment',[Customer\CustomerController::class, 'deleteAttachment']);
    Route::apiResource('cr-customer',Customer\CustomerController::class);

    Route::apiResource('customer-contact',Customer\CustomerContactController::class);


    // Prospect

    Route::get('prospect-dropdown',[Prospect\ProspectController::class, 'getProspectDropdowns']);
    Route::apiResource('prospect',Prospect\ProspectController::class);

    // CRM DopDown

    Route::get('crm-dropdown',[Company\CompanyController::class, 'getCRMDropdowns']);

    // Lead
    Route::post('save-lead-attachment',[Lead\LeadController::class, 'saveLeadAttachment']);
    Route::post('delete-lead-attachment',[Lead\LeadController::class, 'deleteLeadAttachment']);
    Route::get('lead-dropdown',[Lead\LeadController::class, 'getLeadDropdowns']);
    Route::apiResource('lead',Lead\LeadController::class);

    // Campaign
    Route::apiResource('campaign',Campaign\CampaignController::class);

    Route::apiResource('campaign-detail',Campaign\CampaignDetailController::class);

    // Email Campaign
    Route::get('email-campaign-dropdown',[Campaign\EmailCampaignController::class, 'getEmailCampaignDropdowns']);
    Route::apiResource('email-campaign',Campaign\EmailCampaignController::class);

    // Erp Activity

    Route::get('activity-dropdown',[ErpActivity\ErpActivityController::class, 'getActivityDropDown']);
    Route::get('delete-attachment/{id}',[ErpActivity\ErpActivityController::class, 'deleteErpActivityAttachment']);
    Route::post('activity-attachment',[ErpActivity\ErpActivityController::class, 'addActivityAttachment']);
    Route::apiResource('erp-activity',ErpActivity\ErpActivityController::class);

    // Task
    Route::get('task-dropdown',[Task\TaskController::class, 'getTaskDropdowns']);
    Route::post('update-task-status',[Task\TaskController::class, 'updateTaskStatus']);
    Route::apiResource('task',Task\TaskController::class);

    // Opportunity
    Route::get('opportunity-dropdown',[Opportunity\OpportunityController::class, 'getOpportunityDropdown']);
    Route::post('opportunity-pipeline',[Opportunity\OpportunityController::class, 'getOpportunityPipeline']);
    Route::post('update-stage-rating',[Opportunity\OpportunityController::class, 'updateStageRating']);
    Route::apiResource('opportunity',Opportunity\OpportunityController::class);

    // Task
    Route::get('inquiry-dropdown',[Inquiry\InquiryController::class, 'getInquiryDropdown']);
    Route::apiResource('inquiry',Inquiry\InquiryController::class);

    // Erp Configuration

    Route::apiResource('erp-item-category',ErpConfiguration\ErpItemCategoryController::class);

    // Erp Sub Category
    Route::get('category-dropdown',[ErpConfiguration\ErpItemSubCategoryController::class, 'getCategoryDropDown']);
    Route::apiResource('erp-sub-item-category',ErpConfiguration\ErpItemSubCategoryController::class);

    // Erp Item
    Route::get('item-dropdown',[ErpConfiguration\ErpItemController::class, 'getItemDropDown']);
    Route::apiResource('erp-item',ErpConfiguration\ErpItemController::class);

    // Supplier
    Route::get('supplier-dropdown',[Supplier\SupplierController::class, 'getSupplierDropDown']);
    Route::apiResource('supplier',Supplier\SupplierController::class);

    // Quotation
    Route::get('quotation-dropdown',[Quotation\QuotationController::class, 'getQuotationDropDown']);
    Route::post('save-term-condition',[Quotation\QuotationController::class, 'addTermCondition']);
    Route::post('update-term-condition',[Quotation\QuotationController::class, 'updateTermCondition']);

    Route::post('quotation-approval/{item}',[Quotation\QuotationController::class,'sendQuotationForApproval']);
    Route::apiResource('quotation',Quotation\QuotationController::class);

    // Quotation Detail
    Route::apiResource('quotation-detail', Quotation\QuotationDetailController::class);

    // Comments

    Route::apiResource('comments', \App\Http\Controllers\Api\V1\CommentController::class);

    // Attachments
    Route::apiResource('attachments', \App\Http\Controllers\Api\V1\AttachmentController::class);

    // RFP
    Route::get('rfp-dropdown',[RFP\RfpController::class, 'getRfpDropDown']);
    Route::post('rfp-item',[RFP\RfpController::class, 'addRfpItems']);
    Route::post('delete-rfp-item',[RFP\RfpController::class, 'deleteRfpItem']);
    Route::post('send-for-pricing',[RFP\RfpController::class, 'sendForPricing']);
    Route::post('submit-pricing',[RFP\RfpController::class, 'submitPricing']);
    Route::apiResource('rfp', RFP\RfpController::class);

    // RFQ
    Route::get('rfq-dropdown',[RFQ\RfqController::class, 'getRfqDropDown']);
    Route::post('rfq-item',[RFQ\RfqController::class, 'addRfqItems']);
    Route::post('delete-rfq-item',[RFQ\RfqController::class, 'deleteRfqItem']);
    Route::apiResource('rfq', RFQ\RfqController::class);

    // ERP Purchase Order
    Route::get('erp-po-dropdown',[ErpPurchaseOrder\ErpPurchaseOrderController::class, 'getErpPoDropDown']);
    Route::post('erp-po-item',[ErpPurchaseOrder\ErpPurchaseOrderController::class, 'addErpPoItems']);
    Route::post('delete-erp-po-item',[ErpPurchaseOrder\ErpPurchaseOrderController::class, 'deleteErpPoItem']);
    Route::apiResource('erp-purchase-order', ErpPurchaseOrder\ErpPurchaseOrderController::class);

    // Sales Order
    Route::get('sales-order-dropdown',[SalesOrder\SalesOrderController::class, 'getSalesOrderDropDown']);
    Route::post('sales-order-item',[SalesOrder\SalesOrderController::class, 'addSalesOrderItems']);
    Route::post('delete-sales-order-item',[SalesOrder\SalesOrderController::class, 'deleteSalesOrderItem']);
    Route::apiResource('sales-order', SalesOrder\SalesOrderController::class);


    // ERP Dashboard
    Route::get('erp-dashboard',[Dashboard\ErpDashboardController::class, 'erpDashboardStats']);

    // Division
    Route::post('division-employee',[Division\DivisionController::class, 'addDivisionEmployee']);
    Route::post('delete-division-employee',[Division\DivisionController::class, 'deleteDivisionEmployee']);
    Route::get('division-dropdown',[Division\DivisionController::class, 'divisionDropDown']);
    Route::apiResource('division', Division\DivisionController::class);

    // Lead Qualifications
    Route::post('lead-qualification-approval/{item}',[Lead\LeadQualificationController::class,'sendLeadQualificationForApproval']);
    Route::apiResource('lead-qualification',Lead\LeadQualificationController::class);

    // Scale Rating
    Route::get('scale-dropdown',[Configuration\ScaleRatingController::class, 'getScaleDropDown']);
    Route::apiResource('scale-rating',Configuration\ScaleRatingController::class);


    // Sales Team
    Route::post('sales-team-employee',[SalesTeam\SalesTeamController::class, 'addSalesTeamEmployee']);
    Route::post('delete-sales-team-employee',[SalesTeam\SalesTeamController::class, 'deleteSalesTeamEmployee']);
    Route::apiResource('sales-team', SalesTeam\SalesTeamController::class);



});
