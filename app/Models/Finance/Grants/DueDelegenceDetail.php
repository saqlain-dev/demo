<?php

namespace App\Models\Finance\Grants;

use App\Models\User;
use App\Traits\LogEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class DueDelegenceDetail extends Model
{
    use SoftDeletes, LogEvents;

    protected $guarded = ['id'];

    public function DueDelegenceId():BelongsTo
    {
        return $this->belongsTo(DueDelegence::class, 'due_delegence_id');
    }

    public function NofoDetailId():BelongsTo
    {
        return $this->belongsTo(NofoDetail::class, 'nofo_detail_id','id','nofo_details');
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
