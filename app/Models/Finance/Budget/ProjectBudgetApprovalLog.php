<?php

namespace App\Models\Finance\Budget;

use App\Models\Designation;
use App\Models\Program\Project\ProjectProfile;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectBudgetApprovalLog extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];
    
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'log_updated_by')->select(['id', 'name']);
    }

    public function designation(): BelongsTo
    {
        return $this->BelongsTo(Designation::class,'designation_id')->select(['id','name']);
    }
}
