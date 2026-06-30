<?php

namespace App\Models\Finance\SubGrants;

use App\Models\Employee;
use App\Models\Finance\Grants\Nofo;
use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubGrantAppreciation extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function SubGrantId():BelongsTo
    {
        return $this->belongsTo(SubGrant::class, 'sub_grant_id');
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
}
