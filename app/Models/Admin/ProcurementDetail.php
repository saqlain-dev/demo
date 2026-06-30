<?php

namespace App\Models\Admin;

use App\Models\Finance\Budget\ProjectBudgetDetail;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Program\Project\ProjectProfile;
use App\Models\TypeValue;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcurementDetail extends Model
{
    use LogEvents, SoftDeletes;

    protected $guarded = ['id'];

    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectProfile::class, 'project_id')->select('id','project_name','created_by','updated_by');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class,'item_category');
    }
    public function selectionMethod(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'selection_method')->select('id','name');
    }
    public function amountType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'amount_type')->select('id','name');
    }

    public function procurementMethod(): BelongsTo
    {
        return $this->belongsTo(RfqType::class, 'procurement_method');
    }

    public function qualificationType(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class,'qualification_type_id')->select('id','name');
    }
    public function HeadId():BelongsTo
    {
        return  $this->belongsTo(ChartOfAccount::class,'account_id');
    }
    public function budgetDetail():BelongsTo
    {
        return  $this->belongsTo(ProjectBudgetDetail::class,'budget_details_id');
    }
}
