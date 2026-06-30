<?php

namespace App\Models;

use App\Models\Admin\Procurement;
use App\Models\Admin\ProcurementDetail;
use App\Models\Admin\PurchaseRequestRfq;
use App\Models\Admin\Tender;
use App\Models\Program\Project\ProjectProfile;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequest extends Model
{
    use HasFactory,LogEvents,SoftDeletes;
    protected $guarded=['id'];

    public function prItems(): HasMany
    {
        return $this->hasMany(PurchaseRequestDetail::class,'purchase_request_id');
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class,'category_id');
    }
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(ItemSubCategory::class,'sub_category_id');
    }

    public static function getApprovedPRs(): Collection
    {
        return self::query()->where('pr_approval_status',\STATUS::APPROVED)->with('department')->get();
    }
    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class,'project_id');
    }
    public function department(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'department_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select('id','name', 'employee_id');
    }

    public function procurementPlan(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id');
    }

    public function procurementDetail()
    {
        return $this->belongsTo(ProcurementDetail::class, 'procurement_item_id');
    }

    public function assignTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class,'assign_to');
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(PurchaseRequestRfq::class,'purchase_request_id');
    }

    public function tenders(): HasMany
    {
        return $this->hasMany(Tender::class,'purchase_request_id');
    }
}
