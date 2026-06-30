<?php

namespace App\Models\Finance\AdminInvoice;

use App\Models\Finance\Budget\ProjectBudget;
use App\Models\Finance\ChartOfAccount\ChartOfAccount;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminInvoice extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded=['id'];

    public function CategoryId(): BelongsTo
    {
        return $this->belongsTo(TypeValue::class, 'category_id');
    }

    public function headId(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class,'head_id');
    }

    public function budgetId(): BelongsTo
    {
        return $this->belongsTo(ProjectBudget::class,'budget_id');
    }

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }
}
