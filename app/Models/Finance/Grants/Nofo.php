<?php

namespace App\Models\Finance\Grants;

use App\Models\Donar\DonarProfile;
use App\Models\TypeValue;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nofo extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function donor_id(): BelongsTo
    {
        return $this->belongsTo(DonarProfile::class, 'donor_id');
    }

    public function NofoDetail():HasMany
    {
        return $this->hasMany(NofoDetail::class, 'nofo_id');
    }

    public function DDelegence(): HasOne
    {
        return $this->hasOne(DueDelegence::class, 'nofo_id');
    }
    public function ProposalDetail(): HasOne
    {
        return $this->hasOne(GrantProposal::class, 'nofo_id');
    }

    public function ContractDetail(): HasOne
    {
        return $this->hasOne(GrantContract::class, 'nofo_id');
    }

    public function FundRequestDetail(): HasOne
    {
        return $this->hasOne(GrantFundRequest::class, 'nofo_id');
    }

    public function FinancialReport(): HasOne
    {
        return $this->hasOne(GrantFinancialReport::class, 'nofo_id');
    }

    public function CloseOutDetail(): HasOne
    {
        return $this->hasOne(GrantCloseOut::class, 'nofo_id');
    }

    public function AppreciationLetter(): HasOne
    {
        return $this->hasOne(GrantAppreciationLetter::class, 'nofo_id');
    }
    public function LogFramework(): HasOne
    {
        return $this->hasOne(GrantLogFramework::class, 'nofo_id');
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
