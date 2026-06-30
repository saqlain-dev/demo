<?php

namespace App\Models;

use App\Models\Admin\Fleet\AssignVehicle;
use App\Models\Admin\ItemVariant;
use App\Models\Configuration\DraftLetter;
use App\Models\Configuration\GeneratedLetter;
use App\Models\HR\Complaint\Complaint;
use App\Models\HR\Payscale\PayscaleGrading;
use App\Models\HR\Recruitment\ParentEmployeeContract;
use App\Models\HR\TimeSheet\EmployeeTimesheet;
use App\Traits\LogEvents;
use Ramsey\Collection\Collection;
use App\Models\ExitEmployeeDetail;
use App\Models\HR\Payscale\Payscale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\HR\Payroll\EmployeeSalarySetup;
use App\Models\Program\Project\ProjectProfile;
use App\Models\Configuration\EmployeeChangeLog;
use App\Models\HR\Appraisal\PerformanceFactor;
use App\Models\HR\Appraisal\PerformancePlanning;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HR\Payroll\EmployeeAllowanceDeduction;
use App\Models\HR\Payscale\SalaryRange;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Employee extends Model
{
    use HasFactory, LogEvents, SoftDeletes;
    protected $guarded = ['id'];
    protected $casts = [
        'date_of_birth' => 'date:Y-m-d', // Format the 'date' attribute as 'Y-m-d'
        'cnic_issuance' => 'date:Y-m-d', // Format the 'date' attribute as 'Y-m-d'
        'cnic_expiry' => 'date:Y-m-d', // Format the 'date' attribute as 'Y-m-d'
        'date_of_joining' => 'date:Y-m-d', // Format the 'date' attribute as 'Y-m-d'
        'project_id' => 'array', // Cast project_id to an array
    ];
    public function shift(): BelongsTo
    {
        return $this->BelongsTo(Shift::class,'shift_id')->select(['id','shift_name']);
    }

    public function EmployeeSalary(): HasOne
    {
        return $this->HasOne(EmployeeSalarySetup::class,'employee_id');
    }
    public function PayscaleLevel(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'payscale_level')->select(['id','name']);
    }
    public function religiousSect(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'religious_sect')->select(['id','name']);
    }
    public function marital(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'marital_id')->select(['id','name']);
    }
    public function salutation(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'salutation')->select(['id','name']);
    }
    public function employeeTyp(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'employee_type')->select(['id','name']);
    }
    public function department(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'department_id')->select(['id','name']);
    }
    public function bloodGroupName(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'blood_group')->select(['id','name']);
    }
    public function parentage(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'parentage_id')->select(['id','name']);
    }
    public function religion(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'religion_id')->select(['id','name']);
    }
    public function gender(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'gender_id')->select(['id','name']);
    }
    public function referenceName(): BelongsTo
    {
        return $this->BelongsTo(TypeValue::class,'reference_id')->select(['id','name']);
    }
    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class,'created_by')->select(['id','name']);
    }

    public function userProfile(): HasOne
    {
        return $this->hasOne(User::class,'employee_id');
    }
    public function reportTo(): BelongsTo
    {
        return $this->BelongsTo(Employee::class,'report_to_id');
    }
    public function district(): BelongsTo
    {
        return $this->BelongsTo(District::class)->select(['id','name']);
    }
    public function headOffice(): BelongsTo
    {
        return $this->BelongsTo(HeadOffice::class)->select(['id','name']);
    }
    public function branchOffice(): BelongsTo
    {
        return $this->BelongsTo(BranchOffice::class,'branch_office_id')->select(['id','name']);
    }
    public function designation(): BelongsTo
    {
        return $this->BelongsTo(Designation::class,'designation_id')->select(['id','name']);
    }
    public function report(): BelongsTo
    {
        return $this->BelongsTo(Designation::class,'report_to_id')->select(['id','name']);
    }
    public function qualification():HasMany
    {
        return $this->hasMany(Qualification::class);
    }
    public function experience():HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function salarySetup(): HasOne
    {
        return $this->hasOne(EmployeeSalarySetup::class,'employee_id');
    }

    public function employeeAllowanceDeduction(): HasMany
    {
        return $this->hasMany(EmployeeAllowanceDeduction::class,'employee_id');
    }

    public function employeeChnageStatus(): HasMany
    {
        return $this->hasMany(EmployeeChangeLog::class,'EmployeeID');
    }

    public function grade(): BelongsTo
    {
        return $this->BelongsTo(PayscaleGrading::class,'grade');
    }

    public function ProjectId()
    {
        return $this->belongsToMany(ProjectProfile::class, 'project_id', 'id');
    }

    public function payScale(): HasMany
    {
        return $this->hasMany(Payscale::class, 'grading', 'grade');
    }
    public function getProjectDetailsAttribute()
    {
        // Ensure project_id is an array and contains valid IDs

        if ($this->project_id && is_array($this->project_id)) {

            return ProjectProfile::whereIn('id', $this->project_id)->get();
        }

        return collect(); // Return an empty collection if no valid project IDs
    }

    public function exitEmployeeDetail(): BelongsTo
    {
        return $this->BelongsTo(ExitEmployeeDetail::class,'exit_employee_detail_id');
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class,'employee_id');
    }

    public function gratuityCalculationDetails(): HasMany
    {
        return $this->hasMany(GratuityCalculationDetail::class, 'employee_id', 'id');
    }

    public function latestGratuityCalculation(): HasOne
    {
        return $this->hasOne(GratuityCalculationDetail::class, 'employee_id', 'id')
            ->latestOfMany(); // Fetch the latest related record
    }

    public function empContract(): HasOne
    {
        return $this->HasOne(ParentEmployeeContract::class, 'employee_id', 'id');
    }
    public function empContracts(): HasMany
    {
        return $this->hasMany(ParentEmployeeContract::class, 'employee_id', 'id');
    }

    public function employeeTimesheet(): HasMany
    {
        return $this->hasMany(EmployeeTimesheet::class, 'employeeID');
    }

    public function draftLetter(): HasMany
    {
        return $this->hasMany(DraftLetter::class, 'employee_id');
    }
    public function generatedLetter(): HasMany
    {
        return $this->hasMany(GeneratedLetter::class, 'employee_id');
    }

    public function employeeInventory(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'assign_to_emp');
    }

    public function complainAgainsts(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complain_against_emp');
    }
    public function complainFromEmployees(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complain_from_emp');
    }

    public function latestAssignedVehicle()
    {
        return $this->hasOne(AssignVehicle::class, 'driver_id', 'id')->latestOfMany();
    }

    public function empSalary(): HasOne
    {
        return $this->hasOne(EmployeeSalarySetup::class, 'employee_id', 'id');
    }

    public function latestPayScale(): HasOne
    {
        return $this->hasOne(Payscale::class, 'grading', 'grade')->latestOfMany();
    }

    public function performancePlanning(): HasMany
    {
        return $this->hasMany(PerformancePlanning::class, 'employee_id', 'id');
    }

}
