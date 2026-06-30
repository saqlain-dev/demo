<?php

namespace App\Models\Finance\SubGrants;

use App\Models\Employee;
use App\Models\Finance\Grants\Nofo;
use App\Models\Finance\LasInvoice;
use App\Models\Program\ProjectImplementingPartner;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubGrant extends Model
{
    use SoftDeletes, LogEvents;
    protected $guarded = ['id'];

    public function NofoId():BelongsTo
    {
        return $this->belongsTo(Nofo::class, 'nofo_id');
    }
    public function PartnerId():BelongsTo
    {
        return $this->belongsTo(ProjectImplementingPartner::class, 'partner_id','id','project_implementing_partners');
    }

    public function DDelegence():HasOne
    {
        return $this->hasOne(SubGrantDueDeligence::class,'sub_grant_id');
    }
    public function SgProposal(): HasOne
    {
        return $this->hasOne(SubGrantProposal::class, 'sub_grant_id');
    }

    public function SgLogFramework(): HasOne
    {
        return $this->hasOne(SubGrantLogFramework::class, 'sub_grant_id');
    }

    public function SgContract(): HasOne
    {
        return $this->hasOne(SubGrantContract::class, 'sub_grant_id');
    }

    public function SgFundRequest(): HasOne
    {
        return $this->hasOne(SubGrantFundRequest::class, 'sub_grant_id');
    }

    public function SgFinancialReport(): HasMany
    {
        return $this->hasMany(SubGrantFinancialReport::class, 'sub_grant_id');
    }

    public function SgCloseOut(): HasOne
    {
        return $this->hasOne(SubGrantCloseOut::class, 'sub_grant_id');
    }
    public function SgAppriciation(): HasOne
    {
        return $this->hasOne(SubGrantAppreciation::class, 'sub_grant_id');
    }
    public function DraftBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'draft_by');
    }
    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }
    public function updated_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->select(['id', 'name']);
    }

    public function subGrantBudget(): HasMany
    {
        return $this->hasMany(SubGrantBudget::class, 'sub_grant_id');
    }

    public function lasInvoice(): HasMany
    {
        return $this->hasMany(LasInvoice::class, 'sub_grant_id');
    }
}
